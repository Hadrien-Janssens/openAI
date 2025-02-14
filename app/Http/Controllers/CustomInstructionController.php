<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

        $conversations = Conversation::where('user_id', Auth::id())->get();


        return Inertia::render('CustomInstruction/Index', [
            'models' => $models,
            'selectedModel' => $selectedModel,
            'user' => Auth::user(),
            'conversations' => $conversations
        ]);
    }

    public function aboutInstructions(Request $request)
    {
        // update du champ "about_instruction" du user
        auth()->user()->update(['about_instruction' => $request->aboutInstruction]);

        return redirect()->back();
    }

    public function comportementInstructions(Request $request)
    {
        // update du champ "comportement_instruction" du user
        auth()->user()->update(['comportement_instruction' => $request->comportementInstruction]);

        return redirect()->back();
    }
}
