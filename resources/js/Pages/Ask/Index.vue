<template>
    <div class="flex min-h-[100dvh] overflow-hidden bg-gray-50">
        <!-- BAR LATERAL  -->
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />

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
            <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
                <div class="w-full h-full max-w-3xl mx-auto">
                    <!-- Container pour le centrage vertical -->
                    <div
                        class="flex flex-col items-center justify-center h-full"
                    >
                        <div class="mb-5 text-3xl font-extrabold text-center">
                            Comment puis-je vous aider ?
                        </div>
                        <!-- Déplacer le formulaire ici quand messages.length === 0 -->
                        <div class="w-full">
                            <form @submit.prevent="submitPrompt">
                                <div
                                    class="flex flex-col p-2 bg-zinc-100 rounded-3xl"
                                >
                                    <textarea
                                        v-model="message"
                                        rows="2"
                                        class="w-full p-4 bg-transparent border-none resize-none rounded-3xl focus:outline-none focus:ring-0"
                                        placeholder="Écrivez votre message ici..."
                                        @keydown="
                                            (e) => {
                                                if (
                                                    e.key === 'Enter' &&
                                                    !e.shiftKey
                                                ) {
                                                    e.preventDefault();
                                                    submitPrompt();
                                                }
                                            }
                                        "
                                    ></textarea>
                                    <div
                                        class="flex items-center justify-end pl-5"
                                    >
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
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import "highlight.js/styles/github.css"; // Ajout du style de highlight.js
import MenuBar from "@/Components/MenuBar.vue";
import TopMenuBar from "@/Components/TopMenuBar.vue";

const props = defineProps({
    models: Array,
    selectedModel: String,
    flash: Object,
    user: Object,
    conversations: Array,
});

const message = ref("");
const selectedAIModel = ref(props.selectedModel);
const isMenuOpen = ref(true);
const messagesContainer = ref(null);
const conversation_id = ref(null);

const form = useForm({
    message: "",
    model: selectedAIModel.value,
    conversation_id: conversation_id.value,
});

watch(selectedAIModel, (newValue) => {
    form.model = newValue;
});
onMounted(() => {
    if (window.innerWidth < 500) {
        isMenuOpen.value = false;
    }
});

const submitPrompt = () => {
    form.message = message.value;
    form.conversation_id = conversation_id.value;
    form.post(route("ask.create"));
    form.reset();
};
</script>
