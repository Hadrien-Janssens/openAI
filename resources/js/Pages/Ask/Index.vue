<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- BAR LATERAL  -->
        <MenuBar />

        <div class="container mx-auto flex flex-col">
            <!-- TOP BAR MENU -->
            <div class="flex items-center justify-between">
                <select
                    v-model="selectedAIModel"
                    class="rounded-md bg-transparent border-none focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option
                        v-for="model in models"
                        :key="model.name"
                        :value="model.id"
                    >
                        {{ model.name }}
                    </option>
                </select>
                <!-- USER AVATAR  -->
                <div
                    class="rounded-full text-white font-extrabold bg-teal-500 w-8 h-8 flex items-center justify-center"
                >
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
            </div>

            <!-- RESPONSE WINDOW -->
            <div class="max-w-3xl grow flex flex-col justify-center p-5">
                <!-- Affichage de la réponse -->
                <div
                    class="font-extrabold text-3xl text-center mb-5"
                    v-if="messages.length === 0"
                >
                    Comment puis-je vous aider ?
                </div>
                <div
                    v-for="message in messages"
                    class="mb-4 p-4 bg-white rounded-lg shadow"
                    :class="
                        message.who === 'user' ? 'bg-gray-200' : 'bg-gray-300'
                    "
                    v-html="md.render(message.response)"
                ></div>

                <form @submit.prevent="submitPrompt">
                    <!-- Sélection du modèle -->

                    <!-- Zone de texte -->
                    <textarea
                        v-model="message"
                        rows="4"
                        class="w-full rounded-3xl border-none px-4 py-3 resize-none bg-zinc-100"
                        placeholder="Écrivez votre message ici..."
                    ></textarea>

                    <button
                        type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 transition"
                        :disabled="!message"
                    >
                        Envoyer
                    </button>
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
