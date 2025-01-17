<template>
    <div class="flex items-center justify-between p-4">
        <div>
            <i
                v-if="!isMenuOpen"
                class="fa-solid fa-table-columns text-xl text-gray-600"
                @click="$emit('update:isMenuOpen', !isMenuOpen)"
            ></i>
            <select
                v-model="selectedAIModel"
                class="rounded-md bg-transparent border-none focus:outline-none focus:ring-0"
            >
                <option
                    v-for="model in models"
                    :key="model.name"
                    :value="model.id"
                >
                    {{ model.name }}
                </option>
            </select>
        </div>
        <!-- USER AVATAR  -->
        <div
            class="rounded-full text-white font-extrabold bg-cyan-600 w-8 h-8 flex items-center justify-center"
        >
            <DropdownMenu>
                <DropdownMenuTrigger>{{
                    user.name.charAt(0).toUpperCase()
                }}</DropdownMenuTrigger>
                <DropdownMenuContent>
                    <DropdownMenuLabel>Préférences</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <Link :href="route('customInstruction.index')"
                            >custom instruction</Link
                        >
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>

<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Link } from "@inertiajs/vue3";

defineProps({
    models: Array,
    user: Object,
    isMenuOpen: Boolean,
});
const selectedAIModel = defineModel();

defineEmits(["update:isMenuOpen"]);
</script>
