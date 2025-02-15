<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import Textarea from "@/Components/ui/textarea/Textarea.vue";
import Toaster from "@/Components/ui/toast/Toaster.vue";
import { useToast } from "@/components/ui/toast/use-toast";
import {
    Stepper,
    StepperItem,
    StepperSeparator,
    StepperTitle,
    StepperTrigger,
} from "@/Components/ui/stepper";
// import { toast } from "@/Components/ui/toast";
import { Check, Circle, Dot } from "lucide-vue-next";
import { h, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    aboutInstruction: String,
    comportementInstruction: String,
    commandeInstruction: String,
});

const stepIndex = ref(1);

const steps = [
    {
        step: 1,
        title: "A Propos",
    },
    {
        step: 2,
        title: "Comportement",
    },
    {
        step: 3,
        title: "Commande",
    },
];

const form = useForm({
    aboutInstruction: props.aboutInstruction,
    comportementInstruction: props.comportementInstruction,
    commandeInstruction: props.commandeInstruction,
});
const { toast } = useToast();

const submit = () => {
    console.log("submit");
    form.post("/custom-instruction", {
        onSuccess: () => {
            stepIndex.value++;
            toast({
                title: "Génial 🎉",
                description:
                    "Vos informations ont été enregistrées avec succès.",
            });
        },
    });
};
</script>

<template>
    <Stepper
        v-slot="{ isNextDisabled, isPrevDisabled, nextStep, prevStep }"
        v-model="stepIndex"
        class="block w-full"
    >
        <div class="flex w-full gap-2 flex-start">
            <StepperItem
                v-for="step in steps"
                :key="step.step"
                v-slot="{ state }"
                class="relative flex flex-col items-center justify-center w-full"
                :step="step.step"
            >
                <StepperSeparator
                    v-if="step.step !== steps[steps.length - 1].step"
                    class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                />

                <StepperTrigger as-child>
                    <Button
                        :variant="
                            state === 'completed' || state === 'active'
                                ? 'default'
                                : 'outline'
                        "
                        size="icon"
                        class="z-10 rounded-full shrink-0"
                        :class="[
                            state === 'active' &&
                                'ring-2 ring-ring ring-offset-2 ring-offset-background',
                        ]"
                    >
                        <Check v-if="state === 'completed'" class="size-5" />
                        <Circle v-if="state === 'active'" />
                        <Dot v-if="state === 'inactive'" />
                    </Button>
                </StepperTrigger>

                <div class="flex flex-col items-center mt-5 text-center">
                    <StepperTitle
                        :class="[state === 'active' && 'text-primary']"
                        class="text-sm font-semibold transition lg:text-base"
                    >
                        <p class="text-xs text-gray-800">
                            Etape {{ step.step }}
                        </p>
                        <p class="text-xs text-gray-500">{{ step.title }}</p>
                    </StepperTitle>
                </div>
            </StepperItem>
        </div>

        <div class="flex flex-col gap-4">
            <template v-if="stepIndex === 1">
                <div class="w-full p-10 pb-0">
                    <div class="mt-10">
                        <h3 class="mb-2 text-lg font-bold">
                            Etape 1 : A propos de vous
                        </h3>
                        <h4 class="mb-2 text-sm">
                            Ces informations aide l'assistant à adapter ses
                            réponses et suggestions à votre contexte.
                        </h4>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Textarea
                            v-model="form.aboutInstruction"
                            name=""
                            id=""
                            rows="6"
                            class="w-full border resize-none"
                            placeholder="Exemple : Je suis un développeur web spécialisé dans le développement d'applications mobiles. J'ai une expérience de 5 ans dans le domaine et je suis passionné par les nouvelles technologies."
                        ></Textarea>
                        <div class="flex items-center justify-between mt-4">
                            <Button
                                :disabled="isPrevDisabled"
                                variant="outline"
                                size="sm"
                                @click="prevStep()"
                            >
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </Button>
                            <div class="flex items-center gap-3">
                                <Button
                                    v-if="stepIndex !== 3"
                                    :disabled="isNextDisabled"
                                    size="sm"
                                    @click="nextStep()"
                                >
                                    Suivant
                                    <i class="fa-solid fa-arrow-right"></i>
                                </Button>
                                <Button
                                    v-if="stepIndex === 3"
                                    size="sm"
                                    type="submit"
                                >
                                    Submit
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="stepIndex === 2">
                <div class="w-full p-10 pb-0">
                    <div class="mt-10">
                        <h3 class="mb-2 text-lg font-bold">
                            Etape 2 :Comportement de l'assistant
                        </h3>
                        <h4 class="mb-2 text-sm">
                            Définissez comment vous souhaitez que l'assistant
                            interagisse avec vous. Cela comprend le ton des
                            réponses, leur format, etc.
                        </h4>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Textarea
                            v-model="form.comportementInstruction"
                            name=""
                            id=""
                            rows="6"
                            class="w-full border resize-none"
                            placeholder="Exemple : Je préfère que l'assistant réponde de manière concise et directe. J'aime les réponses courtes et précises, sans trop de détails."
                        ></Textarea>
                        <div class="flex items-center justify-between mt-4">
                            <Button
                                :disabled="isPrevDisabled"
                                variant="outline"
                                size="sm"
                                @click="prevStep()"
                            >
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </Button>
                            <div class="flex items-center gap-3">
                                <Button
                                    v-if="stepIndex !== 3"
                                    :disabled="isNextDisabled"
                                    size="sm"
                                    @click="nextStep()"
                                >
                                    Suivant
                                    <i class="fa-solid fa-arrow-right"></i>
                                </Button>
                                <Button v-if="stepIndex === 3" size="sm">
                                    Valider
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="stepIndex === 3">
                <div class="w-full p-10 pb-0">
                    <div class="mt-10">
                        <h3 class="mb-2 text-lg font-bold">
                            Etape 3 : Créer des commandes personnalisées
                        </h3>
                        <h4 class="mb-2 text-sm">
                            Les commandes personnalisées vous permettent de
                            définir des interactions spécifiques avec
                            l'assistant, simplifiant et accélérant l'accès à
                            l'information ou l'exécution de tâches récurrentes.
                        </h4>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Textarea
                            v-model="form.commandeInstruction"
                            name=""
                            id=""
                            rows="6"
                            class="w-full border resize-none"
                            placeholder="Exemple : "
                        ></Textarea>
                        <div class="flex items-center justify-between mt-4">
                            <Button
                                :disabled="isPrevDisabled"
                                variant="outline"
                                size="sm"
                                @click="prevStep()"
                            >
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </Button>
                            <div class="flex items-center gap-3">
                                <Button
                                    v-if="stepIndex !== 3"
                                    :disabled="isNextDisabled"
                                    size="sm"
                                    @click="nextStep()"
                                >
                                    Suivant
                                    <i class="fa-solid fa-arrow-right"></i>
                                </Button>
                                <Button
                                    v-if="stepIndex === 3"
                                    size="sm"
                                    @click="submit"
                                >
                                    Valider
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="stepIndex === 4">
                <div class="w-full p-10 pb-0">
                    <div class="mt-10 text-center">
                        <div
                            class="mb-8 text-6xl text-green-500 animate-bounce"
                        >
                            <i class="fa-solid fa-circle-check" />
                        </div>
                        <h3 class="mb-2 text-lg font-bold">Terminé</h3>
                        <h4 class="mb-2 text-sm text-gray-600">
                            Vos instructions personnalisées ont été enregistrées
                            avec succès.
                        </h4>
                        <div
                            class="flex items-center justify-center gap-4 mt-8"
                        >
                            <Link :href="route('customInstruction.index')">
                                <SecondaryButton>Recommencer</SecondaryButton>
                            </Link>
                            <Link :href="route('ask.index')">
                                <Button>Nouveau chat</Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </Stepper>
    <Toaster />
</template>
