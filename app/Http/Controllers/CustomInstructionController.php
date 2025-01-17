<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomInstructionController extends Controller
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

        return Inertia::render('CustomInstruction/Index', [
            'conversations' => $conversations,
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => Auth::user(),
        ]);
    }
}
