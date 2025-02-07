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
                    <!-- Container pour le centrage vertical -->
                    <div
                        class="h-full flex flex-col justify-center items-center"
                    >
                        <div class="font-extrabold text-3xl text-center mb-5">
                            Comment puis-je vous aider ?
                        </div>
                        <!-- Déplacer le formulaire ici quand messages.length === 0 -->
                        <div class="w-full">
                            <form @submit.prevent="submitPrompt">
                                <div
                                    class="bg-zinc-100 rounded-3xl p-2 flex flex-col"
                                >
                                    <textarea
                                        v-model="message"
                                        rows="2"
                                        class="w-full rounded-3xl p-4 border-none resize-none focus:outline-none bg-transparent focus:ring-0"
                                        placeholder="Écrivez votre message ici..."
                                    ></textarea>
                                    <div
                                        class="flex justify-between items-center pl-5"
                                    >
                                        <input
                                            type="file"
                                            ref="fileInput"
                                            class="hidden"
                                            accept="image/*"
                                            @change="handleFileSelect"
                                        />
                                        <i
                                            class="fa-regular fa-image text-gray-500 text-2xl hover:cursor-pointer"
                                            @click="$refs.fileInput.click()"
                                        ></i>
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
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Messages existants -->
                    <!-- <div v-else class="flex flex-col space-y-4">
                        <div
                            v-for="message in messages"
                            class="mb-4 p-4 py-3"
                            :class="
                                message.who === 'user'
                                    ? 'bg-zinc-100 rounded-full self-end'
                                    : ''
                            "
                            v-html="md.render(message.response)"
                        ></div>
                    </div> -->
                </div>
            </div>

            <!-- Formulaire fixe en bas seulement quand il y a des messages -->
            <!-- <div v-if="messages.length > 0" class="flex-none p-4">
                <div class="w-full max-w-3xl mx-auto">
                    <form @submit.prevent="submitPrompt">
                        <div class="bg-zinc-100 rounded-3xl p-4 flex flex-col">
                            <textarea
                                v-model="message"
                                rows="2"
                                class="w-full p-4 rounded-3xl border-none resize-none focus:outline-none bg-transparent focus:ring-0"
                                placeholder="Écrivez votre message ici..."
                            ></textarea>
                            <div>
                                <i class="fa-regular fa-image"></i>

                                <button
                                    type="submit"
                                    class="rounded-full bg-white w-8 h-8 text-white transition self-end hover:scale-105 hover:cursor-pointer group"
                                    :disabled="!message"
                                >
                                    <i
                                        class="fa-solid fa-arrow-up transition group-hover:scale-110"
                                    ></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/github.css"; // Ajout du style de highlight.js
import MenuBar from "@/components/MenuBar.vue";
import TopMenuBar from "@/components/TopMenuBar.vue";

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
});

const message = ref("");
const selectedAIModel = ref(props.selectedModel);
const messages = ref([]);
const isMenuOpen = ref(true);
const messagesContainer = ref(null);
const conversation_id = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: "smooth",
        });
    }
};

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
};

const submitPrompt = () => {
    const formData = new FormData();
    formData.append("message", message.value);
    formData.append("model", selectedAIModel.value);
    if (conversation_id.value) {
        formData.append("conversation_id", conversation_id.value);
    }
    if (selectedFile.value) {
        formData.append("image", selectedFile.value);
    }

    messages.value.push({ response: message.value, who: "user" });
    nextTick(() => scrollToBottom());

    router.post("/ask", formData, {
        onSuccess: () => {
            message.value = "";
            selectedFile.value = null;
            if (fileInput.value) {
                fileInput.value.value = "";
            }
        },
        preserveScroll: true,
    });
};

watch(
    () => props.flash.message,
    (response) => {
        messages.value.push({ response, who: "bot" });
        nextTick(() => scrollToBottom());
    }
);

watch(
    () => props.flash.conversationId,
    (id) => {
        console.log("conversation_id", id);
        conversation_id.value = id;
    }
);
</script>
