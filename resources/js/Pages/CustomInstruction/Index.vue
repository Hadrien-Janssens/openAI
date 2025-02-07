<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    user: Object,
});

const aboutInstruction = ref(props.user.about_instruction);
const comportementInstruction = ref(props.user.comportement_instruction);

const submitAboutForm = () => {
    router.post(
        "/custom-instruction/about",
        {
            aboutInstruction: aboutInstruction.value,
        },
        {
            onSuccess: () => {},
            preserveScroll: true,
        }
    );
};
const submitComportementForm = () => {
    router.post(
        "/custom-instruction/comportement",
        {
            comportementInstruction: comportementInstruction.value,
        },
        {
            onSuccess: () => {},
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <AppLayout title="Profile" :user="user" :conversations="conversations">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Instructions
            </h2>
        </template>

        <div class="w-full p-10 pb-0">
            <div>
                <h3 class="text-lg font-bold mb-2">A propos de vous</h3>
                <h4 class="text-sm mb-2">
                    Cette section est votre espace pour informer l'assistant sur
                    qui vous êtes, vos intérêts, et votre domaine d'expertise.
                    Cette information aide l'assistant à adapter ses réponses et
                    suggestions à votre contexte personnel ou professionnel.
                </h4>
            </div>
            <form @submit.prevent="submitAboutForm">
                <textarea
                    v-model="aboutInstruction"
                    name=""
                    id=""
                    rows="10"
                    class="w-full border"
                ></textarea>
                <SecondaryButton type="submit">Envoyer</SecondaryButton>
            </form>
        </div>

        <div class="w-full p-10 pb-0">
            <div>
                <h3 class="text-lg font-bold mb-2">
                    Comportement de l'assistant
                </h3>
                <h4 class="text-sm mb-2">
                    Ici, vous avez la possibilité de définir comment vous
                    souhaitez que l'assistant interagisse avec vous. Cela
                    comprend le ton des réponses, leur format, et même la
                    manière dont les concepts sont expliqués.
                </h4>
            </div>
            <form action="" @submit.prevent="submitComportementForm">
                <textarea
                    v-model="comportementInstruction"
                    name=""
                    id=""
                    rows="10"
                    class="w-full border"
                ></textarea>
                <SecondaryButton type="submit">Envoyer</SecondaryButton>
            </form>
        </div>

        <div class="w-full p-10 pb-0">
            <div>
                <h3 class="text-lg font-bold mb-2">
                    Créer des commandes personnalisées
                </h3>
                <h4 class="text-sm mb-2">
                    Les commandes personnalisées vous permettent de définir des
                    interactions spécifiques avec l'assistant, simplifiant et
                    accélérant l'accès à l'information ou l'exécution de tâches
                    récurrentes.
                </h4>
            </div>
            <form action="">
                <textarea
                    name=""
                    id=""
                    rows="10"
                    class="w-full border"
                ></textarea>
                <SecondaryButton>Envoyer</SecondaryButton>
            </form>
        </div>
    </AppLayout>
</template>
