<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageStreamed;
use App\Models\Conversation;
use App\Models\Message;
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
        $conversations = Conversation::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();

        $conversation->load('messages');
        return Inertia::render('Ask/Show', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => Auth::user(),
            'messages' => $conversation->messages,
            'conversations' => $conversations,
            'conversation' => $conversation,
        ]);
    }
    public function create(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'title' => 'Nouvelle conversation',
            'current_llm' => auth()->user()->current_llm,
        ]);

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

    public function ask(Request $request)
    {

        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        try {
            //save the message in the database
            if ($request->conversation_id) {
                //create de user message
                Message::create([
                    'role' => 'user',
                    'content' => $request->message,
                    'conversation_id' => $request->conversation_id,
                ]);

                // take history of the conversation
                $conversation = Conversation::find($request->conversation_id);
                $messages = $conversation->messages()
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(fn($msg) => [
                        'role'    => $msg->role,
                        'content' => $msg->content,
                    ])
                    ->toArray();

                $response = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $request->model
                );

                //create de AI message
                Message::create([
                    'role' => 'assistant',
                    'content' => $response,
                    'conversation_id' => $request->conversation_id,
                ]);

                //update the user's current_llm
                auth()->user()->update(['current_llm' => $request->model]);

                //udate curretn_llm in the conversation
                $conversation->update(['current_llm' => $request->model]);

                return redirect()->back()->with([
                    'message' => $response,
                    'conversationId' => $request->conversation_id,
                    'model' => $request->model,
                ]);
            } else {
                $messages = [[
                    'role' => 'user',
                    'content' => $request->message,
                ]];

                $response = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $request->model
                );

                //create a title for the conversation with AI
                $prompt = "Génère un titre de quelques mots ( une phrase maximum ) pour cette conversation ";

                $messages = [
                    ...$messages,
                    [
                        'role' => 'assistant',
                        'content' => $response,
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ];

                $title = (new ChatService())->sendMessage(
                    messages: $messages,
                    model: $request->model
                );


                //update the user's current_llm
                auth()->user()->update(['current_llm' => $request->model]);

                //create the conversation
                $conversation = Conversation::create([
                    'user_id' => auth()->id(),
                    'title' => $title,
                    'current_llm' => $request->model,
                ]);

                //create de user message
                Message::create([
                    'role' => 'user',
                    'content' => $request->message,
                    'conversation_id' => $conversation->id,
                ]);

                //create de AI message
                Message::create([
                    'role' => 'assistant',
                    'content' => $response,
                    'conversation_id' => $conversation->id,
                ]);

                // return redirect()->back()->with([
                //     'message' => $response,
                //     'conversationId' => $conversation->id,
                // ]);

                return redirect()->route('ask.show', $conversation->id)->with([
                    'model' => $request->model,
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function streamMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model'   => 'nullable|string',
        ]);
        $conversation = Conversation::find($request->conversation_id);
        try {

            if ($request->conversation_id) {
                // 1. Sauvegarder le message de l'utilisateur
                $conversation->messages()->create([
                    'content' => $request->input('message'),
                    'role'    => 'user',
                ]);

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

                // return response()->json("ok");
                return redirect()->route('ask.show', $conversation->id)->with([
                    'model' => $request->model,
                ]);
            } else {
            }
            // else {
            //     // 1. Créer une nouvelle conversation
            //     $conversation = Conversation::create([
            //         'user_id' => auth()->id(),
            //         'title' => $request->input('message'),
            //         'current_llm' => $request->model,
            //     ]);

            //     // 2. Créer le message de l'utilisateur
            //     $conversation->messages()->create([
            //         'content' => $request->input('message'),
            //         'role'    => 'user',
            //     ]);

            //     // 3. Nom du canal
            //     $channelName = "chat.{$conversation->id}";

            //     // 4. Obtenir un flux depuis le ChatService
            //     $stream = (new ChatService())->streamConversation(
            //         messages: [
            //             [
            //                 'role' => 'user',
            //                 'content' => $request->input('message'),
            //             ]
            //         ],
            //         model: $request->model,
            //     );

            //     // 5. Créer le message "assistant" dans la BD (vide pour l'instant)
            //     $assistantMessage = $conversation->messages()->create([
            //         'content' => '',
            //         'role'    => 'assistant',
            //     ]);

            //     // 6. Variables pour accumuler la réponse
            //     $fullResponse = '';
            //     $buffer = '';
            //     $lastBroadcastTime = microtime(true) * 1000; // ms

            //     // 7. Itération sur le flux
            //     foreach ($stream as $response) {
            //         $chunk = $response->choices[0]->delta->content ?? '';

            //         if ($chunk) {
            //             $fullResponse .= $chunk;
            //             $buffer .= $chunk;

            //             // Broadcast seulement toutes les ~100ms
            //             $currentTime = microtime(true) * 1000;
            //             if ($currentTime - $lastBroadcastTime >= 100) {
            //                 broadcast(new ChatMessageStreamed(
            //                     channel: $channelName,
            //                     content: $buffer,
            //                     isComplete: false
            //                 ));

            //                 $buffer = '';
            //                 $lastBroadcastTime = $currentTime;
            //             }
            //         }
            //     }

            //     // 8. Diffuser le buffer restant
            //     if (!empty($buffer)) {
            //         broadcast(new ChatMessageStreamed(
            //             channel: $channelName,
            //             content: $buffer,
            //             isComplete: false
            //         ));
            //     }

            //     // 9. Mettre à jour la BD avec le texte complet
            //     $assistantMessage->update([
            //         'content' => $fullResponse
            //     ]);

            //     // 10. Émettre un dernier événement pour signaler la complétion
            //     broadcast(new ChatMessageStreamed(
            //         channel: $channelName,
            //         content: $fullResponse,
            //         isComplete: true
            //     ));

            //     // return response()->json("ok");
            //     return redirect()->route('ask.show', $conversation->id)->with([
            //         'model' => $request->model,
            //     ]);
            // }
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

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
