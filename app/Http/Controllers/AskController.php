<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageStreamed;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AskController extends Controller
{
    public function index()
    {
        //find model preference
        $user = User::find(Auth::id());

        if ($user->current_llm) {
            $selectedModel = $user->current_llm;
        } else {
            $selectedModel = ChatService::DEFAULT_MODEL;
        }

        $models = (new ChatService())->getModels();
        $conversations = Conversation::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        return Inertia::render('Ask/Index', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => Auth::user(),
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation)
    {
        //find model preference
        $user = User::find(Auth::id());

        if ($conversation->current_llm) {
            $selectedModel = $conversation->current_llm;
        } else if ($user->current_llm) {
            $selectedModel = $user->current_llm;
        } else {
            $selectedModel = ChatService::DEFAULT_MODEL;
        }

        $models = (new ChatService())->getModels();
        $conversations = Conversation::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        $conversation->load('messages');
        return Inertia::render('Ask/Show', [
            'models' => $models,
            'model' => $selectedModel,
            'user' => Auth::user(),
            'messages' => $conversation->messages,
            'conversations' => $conversations,
            'conversation' => $conversation,
        ]);
    }
    public function create(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'Nouvelle conversation',
            'current_llm' => $request->model,
        ]);

        auth()->user()->update(['current_llm' => $request->model]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $request->message,
        ]);

        return redirect()->route('ask.show', $conversation->id)->with([
            'model' => $request->model,
            'new' => true,
            'conversationId' => $conversation->id,
        ]);
    }

    public function streamMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model'   => 'nullable|string',
            'new'   => 'nullable|boolean',
        ]);
        $conversation = Conversation::find($request->conversation_id);


        try {

            if (!$request->new) {
                $conversation->messages()->create([
                    'content' => $request->input('message'),
                    'role'    => 'user',
                ]);
            }

            // 2. Nom du canal
            $channelName = "chat.{$conversation->id}";

            // 3. Récupérer historique
            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($msg) => [
                    'role'    => $msg->role,
                    'content' => $msg->content,
                ])
                ->toArray();

            // 4. Obtenir un flux depuis le ChatService
            $stream = (new ChatService())->streamConversation(
                messages: $messages,
                model: $conversation->model ?? $request->user()->last_used_model ?? ChatService::DEFAULT_MODEL,
            );

            // 5. Créer le message "assistant" dans la BD (vide pour l'instant)
            $assistantMessage = $conversation->messages()->create([
                'content' => '',
                'role'    => 'assistant',
            ]);

            // 6. Variables pour accumuler la réponse
            $fullResponse = '';
            $buffer = '';
            $lastBroadcastTime = microtime(true) * 1000; // ms

            // 7. Itération sur le flux
            foreach ($stream as $response) {
                $chunk = $response->choices[0]->delta->content ?? '';

                if ($chunk) {
                    $fullResponse .= $chunk;
                    $buffer .= $chunk;

                    // Broadcast seulement toutes les ~100ms
                    $currentTime = microtime(true) * 1000;
                    if ($currentTime - $lastBroadcastTime >= 100) {
                        broadcast(new ChatMessageStreamed(
                            channel: $channelName,
                            content: $buffer,
                            isComplete: false
                        ));

                        $buffer = '';
                        $lastBroadcastTime = $currentTime;
                    }
                }
            }

            // 8. Diffuser le buffer restant
            if (!empty($buffer)) {
                broadcast(new ChatMessageStreamed(
                    channel: $channelName,
                    content: $buffer,
                    isComplete: false
                ));
            }

            // 9. Mettre à jour la BD avec le texte complet
            $assistantMessage->update([
                'content' => $fullResponse
            ]);

            // 10. Émettre un dernier événement pour signaler la complétion
            broadcast(new ChatMessageStreamed(
                channel: $channelName,
                content: $fullResponse,
                isComplete: true
            ));

            return redirect()->route('ask.show', $conversation->id)->with([
                'model' => $request->model,
            ]);
        } catch (\Exception $e) {
            // Diffuser l’erreur
            if (isset($conversation)) {
                broadcast(new ChatMessageStreamed(
                    channel: "chat.{$conversation->id}",
                    content: "Erreur: " . $e->getMessage(),
                    isComplete: true,
                    error: true
                ));
            }
            return redirect()->route('ask.show', $conversation->id)->with([
                'model' => $request->model,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function updateTitle(Request $request)
    {
        $conversation = Conversation::find($request->conv_id);
        $model = $conversation->current_llm;
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        $prompt = "Génère un titre de quelques mots ( une phrase maximum ) pour cette conversation ";

        $messages = [
            ...$messages,
            [
                'role' => 'user',
                'content' => $prompt,
            ]
        ];

        $haveError = true;

        while ($haveError) {
            try {
                $title = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $model
                );
                $haveError = false;
            } catch (\Throwable $th) {
                return redirect()->route('ask.show', $conversation->id)->with([
                    'model' => $model,
                    'title' => false,
                ]);
            }
        }

        $conversation->update(['title' => $title]);
        return redirect()->route('ask.show', $conversation->id)->with([
            'model' => $model,
            'title' => true,
        ]);
    }
    public function destroy($conversation)
    {
        $conversation = Conversation::find($conversation);
        $conversation->delete();
        return redirect()->route('ask.index');
    }

    public function conversation()
    {
        $conversations = Conversation::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return response()->json($conversations);
    }

    public function deleteTwoLastMessages(Conversation $conversation)
    {
        $conversation->messages()->orderBy('created_at', 'desc')->take(2)->delete();
        return redirect()->route('ask.show', $conversation->id);
    }
}