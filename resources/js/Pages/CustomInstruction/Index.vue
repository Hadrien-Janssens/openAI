<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import MenuBar from "@/Components/MenuBar.vue";
import TopMenuBar from "@/Components/TopMenuBar.vue";
import Stepper from "@/Components/Stepper.vue";
import Button from "@/Components/ui/button/Button.vue";
import Textarea from "@/Components/ui/textarea/Textarea.vue";

const props = defineProps({
    user: Object,
    conversations: Array,
});

const aboutInstruction = ref(props.user.about_instruction);
const comportementInstruction = ref(props.user.comportement_instruction);
const commandeInstruction = ref(props.user.commande_instruction);

const isMenuOpen = ref(true);
</script>

<template>
    <div class="flex min-h-[100dvh] bg-white overflow-hidden">
        <MenuBar v-model="isMenuOpen" :conversations="conversations" />
        <div
            class="flex flex-col w-full h-[100dvh] duration-300"
            :class="isMenuOpen ? 'ml-60' : 'ml-0'"
        >
            <div>
                <TopMenuBar
                    :models="models"
                    :user="user"
                    v-model="selectedAIModel"
                    :isMenuOpen="isMenuOpen"
                    @update:isMenuOpen="isMenuOpen = $event"
                />
                <div class="max-w-3xl p-4 mx-auto">
                    <Stepper
                        :aboutInstruction="aboutInstruction"
                        :comportementInstruction="comportementInstruction"
                        :commandeInstruction="commandeInstruction"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
