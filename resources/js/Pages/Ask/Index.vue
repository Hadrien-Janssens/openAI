<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- BAR LATERAL  -->
        <MenuBar />

        <div class="container mx-auto flex flex-col px-4">
            <!-- TOP BAR MENU -->
            <TopMenuBar
                :models="models"
                :user="user"
                v-model="selectedAIModel"
            />
            <!-- RESPONSE WINDOW -->
            <div
                class="w-full max-w-3xl grow flex flex-col mx-auto py-5"
                :class="
                    messages.length === 0 ? 'justify-center' : 'justify-between'
                "
            >
                <div
                    class="font-extrabold text-3xl text-center mb-5"
                    v-if="messages.length === 0"
                >
                    Comment puis-je vous aider ?
                </div>

                <div class="flex flex-col space-y-4">
                    <div
                        v-for="message in messages"
                        class="mb-4 p-4"
                        :class="
                            message.who === 'user'
                                ? 'bg-zinc-100 rounded-full self-end'
                                : ''
                        "
                        v-html="md.render(message.response)"
                    ></div>
                </div>

                <form @submit.prevent="submitPrompt">
                    <!-- Sélection du modèle -->

                    <!-- Zone de texte -->
                    <div class="bg-zinc-100 rounded-3xl p-2 flex flex-col">
                        <textarea
                            v-model="message"
                            rows="2"
                            class="w-full rounded-3xl border-none resize-none focus:outline-none bg-transparent focus:ring-0"
                            placeholder="Écrivez votre message ici..."
                        ></textarea>

                        <button
                            type="submit"
                            class="rounded-full bg-black w-8 h-8 text-white transition self-end hover:scale-105 hover:cursor-pointer group"
                            :disabled="!message"
                        >
                            <i
                                class="fa-solid fa-arrow-up transition group-hover:scale-110"
                            ></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github.css"; // Ajout du style de highlight.js
import MenuBar from "./components/MenuBar.vue";
import TopMenuBar from "./components/TopMenuBar.vue";

// Actual default values
const md = MarkdownIt({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value;
            } catch (__) {}
        }
        return ""; // use external default escaping
    },
});

const props = defineProps({
    models: Array,
    selectedModel: String,
    flash: Object,
    user: Object,
});

const message = ref("");
const selectedAIModel = ref(props.selectedModel);
const messages = ref([]);

const submitPrompt = () => {
    messages.value.push({ response: message.value, who: "user" });

    router.post(
        "/ask",
        {
            message: message.value,
            model: selectedAIModel.value,
        },
        {
            onSuccess: () => {
                message.value = ""; // Réinitialiser le message après l'envoi
            },
            preserveScroll: true,
        }
    );
};

watch(
    () => props.flash.message,
    (response) => {
        messages.value.push({ response, who: "bot" });
    }
);
</script>
