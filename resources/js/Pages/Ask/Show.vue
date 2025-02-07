<template>
    <div class="min-h-screen bg-gray-50 flex overflow-hidden">
        <!-- BAR LATERAL  -->
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />

        <!-- MAIN  -->
        <div
            class="w-full flex flex-col h-screen duration-300"
            :class="isMenuOpen ? 'ml-60' : 'ml-0'"
        >
            <!-- TOP BAR MENU - Fixed at top -->
            <TopMenuBar
                :models="models"
                :user="user"
                v-model="selectedAIModel"
                :isMenuOpen="isMenuOpen"
                @update:isMenuOpen="isMenuOpen = $event"
            />

            <!-- RESPONSE WINDOW - Scrollable -->
            <div class="flex-1 overflow-y-auto p-4" ref="messagesContainer">
                <div class="w-full max-w-3xl mx-auto h-full">
                    <!-- Messages existants -->
                    <div class="flex flex-col space-y-4">
                        <div
                            v-for="message in conversation.messages"
                            class="mb-4 p-4 py-3"
                            :class="
                                message.role === 'user'
                                    ? 'bg-zinc-100 rounded-full self-end'
                                    : ''
                            "
                            v-html="md.render(message.content)"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Formulaire fixe en bas seulement quand il y a des messages -->
            <div v-if="conversation.messages.length > 0" class="flex-none p-4">
                <div class="w-full max-w-3xl mx-auto">
                    <form @submit.prevent="submitPrompt">
                        <div class="bg-zinc-100 rounded-3xl p-2 flex flex-col">
                            <textarea
                                v-model="message"
                                rows="2"
                                class="w-full p-4 rounded-3xl border-none resize-none focus:outline-none bg-transparent focus:ring-0"
                                placeholder="Écrivez votre message ici..."
                            ></textarea>

                            <i class="fa-regular fa-image"></i>
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
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github.css"; // Ajout du style de highlight.js
import MenuBar from "../../Components/MenuBar.vue";
import TopMenuBar from "../../Components/TopMenuBar.vue";
import { Button } from "@/Components/ui/button";

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
    conversations: Array,
    conversation: Object,
});

const message = ref("");
const selectedAIModel = ref(props.selectedModel);
const messages = ref([]);
const isMenuOpen = ref(true);
const messagesContainer = ref(null);
const localMessages = ref(props.conversation.messages);
const messagestreamer = ref("");

const scrollToBottom = (typeOfscrolling) => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: typeOfscrolling,
        });
    }
};

const submitPrompt = () => {
    messages.value.push({ response: message.value, who: "user" });
    nextTick(() => scrollToBottom("smooth"));

    router.post(
        "/ask",
        {
            message: message.value,
            model: selectedAIModel.value,
            conversation_id: route().params["conversation"],
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
        nextTick(() => scrollToBottom("smooth"));
    }
);

watch(
    () => props.flash.model,
    (model) => {
        selectedAIModel.value = model;
    }
);

watch(
    () => messagestreamer.value,
    (response) => {
        messages.value.push({ response, who: "bot" });
        nextTick(() => scrollToBottom("smooth"));
    }
);

onMounted(() => {
    scrollToBottom("instant");
    const channel = `chat.${props.conversation.id}`;
    console.log("🔌 Tentative de connexion au canal:", channel);

    const subscription = window.Echo.private(channel)
        .subscribed(() => {
            console.log("✅ Connecté avec succès au canal:", channel);
        })
        .error((error) => {
            console.error("❌ Erreur de connexion au canal:", error);
        })
        .listen(".message.streamed", (event) => {
            console.log("📨 Message reçu:", event);

            const lastMessage =
                localMessages.value[localMessages.value.length - 1];

            // Vérifier qu'on ait bien un message assistant en cours
            if (!lastMessage || lastMessage.role !== "assistant") {
                console.log("⚠️ Aucun message assistant ciblé pour concaténer");
                return;
            }

            // Gestion d'erreur éventuelle
            if (event.error) {
                console.error("❌ Erreur reçue:", event.error);
                // On peut retirer le message assistant, avertir l’utilisateur, etc.
                localMessages.value.pop();
                usePage().props.flash.error = event.content;
                return;
            }

            // Dès qu’on reçoit le premier chunk, on peut désactiver un éventuel spinner
            if (lastMessage.isLoading && event.content) {
                console.log("🔄 Premier chunk reçu, on enlève le loading");
                lastMessage.isLoading = false;
            }

            // Ajouter le chunk reçu
            if (!event.isComplete) {
                lastMessage.content += event.content;
                messagestreamer.value = lastMessage.content;
                nextTick(() => scrollToBottom("smooth"));
            }

            // Si c’est la fin, on peut déclencher des actions (comme l’update du titre)
            if (event.isComplete) {
                console.log("✅ Message complet reçu");
                messagestreamer.value = lastMessage.content;
                nextTick(() => scrollToBottom("smooth"));

                if (localMessages.value.length === 2) {
                    // par exemple, générer un titre
                    sidebarRef.value?.updateTitle(props.conversation.id);
                }
            }
            nextTick(() => scrollToBottom("smooth"));
        });

    channelSubscription.value = subscription;
});
</script>
