<template>
    <div class="flex min-h-[100dvh] overflow-hidden bg-white">
        <!-- BAR LATERAL  -->
        <MenuBar
            v-model="isMenuOpen"
            :conversations="conversations"
            :title="title"
        />

        <!-- MAIN  -->
        <div
            class="flex flex-col w-full h-[100dvh] duration-300"
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
            <div class="p-4 overflow-y-auto grow" ref="messagesContainer">
                <div class="w-full h-full max-w-3xl mx-auto">
                    <!-- Messages existants -->
                    <div class="flex flex-col w-full gap-4">
                        <div
                            v-for="message in localMessages"
                            class="flex items-start gap-3 p-4"
                            :class="
                                message.role === 'user'
                                    ? 'bg-gray-100  rounded-3xl  self-end max-w-[70%] '
                                    : ''
                            "
                        >
                            <p
                                v-if="message.role === 'assistant'"
                                class="relative -top-2"
                            >
                                <i class="text-gray-500 fa-solid fa-robot"></i>
                            </p>
                            <span
                                v-html="md.render(message.content)"
                                class="w-full"
                            ></span>
                        </div>

                        <div v-if="loader" class="flex px-4 space-x-2">
                            <div
                                class="w-2 h-3 bg-black rounded-full animate-bounce [animation-duration:0.9s]"
                            ></div>
                            <div
                                class="w-2 h-3 bg-black rounded-full animate-bounce [animation-duration:0.9s] [animation-delay:0.2s]"
                            ></div>
                            <div
                                class="w-2 h-3 bg-black rounded-full animate-bounce [animation-duration:0.9s] [animation-delay:0.4s]"
                            ></div>
                        </div>

                        <div
                            v-if="error"
                            class="flex items-center px-4 space-x-2"
                        >
                            <p class="text-red-500">
                                ❌ Une erreur est survenue. Veuillez réessayer
                            </p>
                            <button
                                @click="submitPrompt"
                                class="w-8 h-8 p-1 border rounded-full"
                            >
                                <i
                                    class="text-gray-600 fa-solid fa-rotate-right"
                                ></i>
                            </button>
                        </div>
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
                                rows="1"
                                class="w-full p-4 bg-transparent border-none resize-none rounded-3xl focus:outline-none focus:ring-0"
                                placeholder="Écrivez votre message ici..."
                                @keydown="
                                    (e) => {
                                        if (e.key === 'Enter' && !e.shiftKey) {
                                            e.preventDefault();
                                            submitPrompt();
                                        }
                                    }
                                "
                            ></textarea>

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
import "highlight.js/styles/github.css";
import MenuBar from "../../Components/MenuBar.vue";
import TopMenuBar from "../../Components/TopMenuBar.vue";

const md = new MarkdownIt({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return `<pre class="w-[270px] mx-auto md:w-[500px] lg:w-[650px] p-4 overflow-x-scroll bg-gray-200 rounded-lg my-3  ">
                        <code class="relative w-full ">
                            <div class='absolute top-0 z-50 px-3 text-gray-500 transition-all bg-gray-200 border border-gray-500 rounded-sm hover:bg-gray-300 hover:cursor-pointer' ><i class="fa-regular fa-clone"></i></div>
                            ${hljs.highlight(str, { language: lang }).value}
                        </code>
                    </pre>`;
            } catch (__) {}
        }
        return `<pre>
                <code class="">${md.utils.escapeHtml(str)}</code>
            </pre>`;
    },
    linkify: true,
    breaks: true,
});

const props = defineProps({
    models: Array,
    flash: Object,
    user: Object,
    conversations: Array,
    conversation: Object,
    model: String,
    error: String,
});

const message = ref("");
const selectedAIModel = ref(props.model);
const isMenuOpen = ref(true);
const messagesContainer = ref(null);
const localMessages = ref(props.conversation.messages);
const loader = ref(false);
const error = ref(false);
const title = ref(false);

const scrollToBottom = (typeOfscrolling) => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: typeOfscrolling,
        });
    }
};
let sentMessage;
const submitPrompt = () => {
    loader.value = true;

    if (!error.value) {
        sentMessage = "";
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

            error.value = false;
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
                conversation_id: props.conversation.id,
            },
            {
                onSuccess: () => {
                    sentMessage = "";
                },
                preserveScroll: true,
            }
        );
    } else {
        sentMessage =
            localMessages.value[localMessages.value.length - 1].content;
        console.log(sentMessage);

        localMessages.value.push({
            content: "",
            role: "assistant",
            isLoading: true,
        });
        error.value = false;

        router.post(
            "/ask",
            {
                message: sentMessage,
                model: selectedAIModel.value,
                new: props.flash.new,
                conversation_id: props.conversation.id,
            },
            {
                onSuccess: () => {
                    sentMessage = "";
                },
                preserveScroll: true,
            }
        );
    }
};
const updateTitle = (conv_id) => {
    router.post("/update-title", {
        conv_id,
    });
};
const deleteTwoLastMessages = () => {
    localMessages.value.pop();
    router.delete(route("ask.deleteTwoLastMessages"), {
        conversation: props.conversation.id,
    });
};

watch(
    () => props.flash.model,
    (model) => {
        selectedAIModel.value = model;
    }
);
watch(
    () => props.flash.title,
    (v) => {
        if (v) {
            title.value = v;
        } else {
            updateTitle(props.conversation.id);
        }
    }
);

onMounted(() => {
    if (window.innerWidth < 500) {
        isMenuOpen.value = false;
    }
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
            const lastMessage =
                localMessages.value[localMessages.value.length - 1];

            if (!lastMessage || lastMessage.role !== "assistant") {
                console.log("⚠️ Aucun message assistant ciblé pour concaténer");
                return;
            }
            if (event.error) {
                loader.value = false;
                error.value = true;
                deleteTwoLastMessages();
                usePage().props.flash.error = event.content;
                setTimeout(() => {
                    nextTick(() => scrollToBottom("smooth"));
                }, 400);
                return;
            }
            if (lastMessage.isLoading && event.content) {
                loader.value = false;
            }

            if (!event.isComplete) {
                lastMessage.content += event.content;
            }

            if (event.isComplete) {
                if (localMessages.value.length === 2) {
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
