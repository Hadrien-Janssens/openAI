<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AskController extends Controller
{
    public function index()
    {
        $models = (new ChatService())->getModels();
        $selectedModel = ChatService::DEFAULT_MODEL;
        $conversations = Conversation::where('user_id', auth()->id())->get();

        return Inertia::render('Ask/Index', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => auth()->user(),
            'conversations' => $conversations,
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
        ]);

        try {
            $messages = [[
                'role' => 'user',
                'content' => $request->message,
            ]];

            $response = (new ChatService())->sendMessage(
                messages: $messages,
                model: $request->model
            );

            //update the user's current_llm
            auth()->user()->update(['current_llm' => $request->model]);

            return redirect()->back()->with('message', $response);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}
