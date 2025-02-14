<script setup lang="ts">
import { Button } from "@/Components/ui/button";

import {
    Stepper,
    StepperItem,
    StepperSeparator,
    StepperTitle,
    StepperTrigger,
} from "@/Components/ui/stepper";
import { Check, Circle, Dot } from "lucide-vue-next";

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
</script>

<template>
    <Stepper class="z-10 flex items-start w-full gap-2 mt-10">
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
                    <p class="text-xs text-gray-800">Etape {{ step.step }}</p>
                    <p class="text-xs text-gray-500">{{ step.title }}</p>
                </StepperTitle>
            </div>
        </StepperItem>
    </Stepper>
</template>
