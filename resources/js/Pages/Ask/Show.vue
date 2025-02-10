<template>
    <div class="flex min-h-screen overflow-hidden bg-gray-50">
        <!-- BAR LATERAL  -->
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />

        <!-- MAIN  -->
        <div
            class="flex flex-col w-full h-screen duration-300"
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
            <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
                <div class="w-full h-full max-w-3xl mx-auto">
                    <!-- Messages existants -->
                    <div class="flex flex-col space-y-4">
                        <div
                            v-for="message in localMessages"
                            class="p-4 py-3 mb-4"
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
                        <div class="flex flex-col p-2 bg-zinc-100 rounded-3xl">
                            <textarea
                                v-model="message"
                                rows="2"
                                class="w-full p-4 bg-transparent border-none resize-none rounded-3xl focus:outline-none focus:ring-0"
                                placeholder="Écrivez votre message ici..."
                            ></textarea>

                            <i class="fa-regular fa-image"></i>
                            <button
                                type="submit"
                                class="self-end w-8 h-8 text-white transition bg-black rounded-full hover:scale-105 hover:cursor-pointer group"
                                :disabled="!message"
                            >
                                <i
                                    class="transition fa-solid fa-arrow-up group-hover:scale-110"
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
import { router, usePage } from "@inertiajs/vue3";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github.css"; // Ajout du style de highlight.js
import MenuBar from "../../Components/MenuBar.vue";
import TopMenuBar from "../../Components/TopMenuBar.vue";

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
    model: String,
});

const message = ref("");
const selectedAIModel = ref(props.selectedModel);
const isMenuOpen = ref(true);
const messagesContainer = ref(null);
const localMessages = ref(props.conversation.messages);

const scrollToBottom = (typeOfscrolling) => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: typeOfscrolling,
        });
    }
};

const submitPrompt = () => {
    let sentMessage = "";

    if (props.flash.new) {
        sentMessage = props.conversation.messages[0].content;

        localMessages.value.push({
            content: "",
            role: "assistant",
            isLoading: true,
        });
    } else {
        localMessages.value.push({
            content: message.value,
            role: "user",
        });

        localMessages.value.push({
            content: "",
            role: "assistant",
            isLoading: true,
        });

        sentMessage = message.value;
        message.value = "";

        nextTick(() => scrollToBottom("smooth"));
    }

    router.post(
        "/ask",
        {
            message: sentMessage,
            model: selectedAIModel.value,
            new: props.flash.new,
            conversation_id: route().params["conversation"],
        },
        {
            onSuccess: () => {
                sentMessage = ""; // Réinitialiser le message après l'envoi
            },
            preserveScroll: true,
        }
    );
};
const updateTitle = (conv_id) => {
    router.post("/ask/update-title", {
        conv_id,
    });
};

watch(
    () => props.flash.model,
    (model) => {
        selectedAIModel.value = model;
    }
);

onMounted(() => {
    if (props.flash.new) {
        submitPrompt();
    }
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
            // console.log("📨 Message reçu:", event);

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
            }

            // Si c’est la fin, on peut déclencher des actions (comme l’update du titre)
            if (event.isComplete) {
                console.log("✅ Message complet reçu");
                if (localMessages.value.length === 2) {
                    console.log(props.conversation.title);
                    updateTitle(props.conversation.id);
                }

                setTimeout(() => {
                    nextTick(() => scrollToBottom("smooth"));
                }, 400);
            }

            nextTick(() => scrollToBottom("smooth"));
        });
});
</script>
