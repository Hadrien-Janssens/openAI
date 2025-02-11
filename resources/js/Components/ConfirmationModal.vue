<template>
    <Transition name="modal-fade">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div
                class="fixed inset-0 bg-black/50"
                @click="$emit('update:modelValue', false)"
            ></div>
            <div
                class="relative z-50 w-full max-w-md p-6 bg-white rounded-lg shadow-lg"
            >
                <div class="flex flex-col space-y-4">
                    <div class="flex items-center space-x-3">
                        <div
                            class="flex items-center justify-center w-8 h-8 p-2 text-red-600 bg-red-100 rounded-full"
                        >
                            <i class="fa-solid fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Confirmer la suppression
                        </h3>
                    </div>
                    <p class="text-gray-500">
                        Êtes-vous sûr de vouloir supprimer cette conversation ?
                        Cette action est irréversible.
                    </p>
                    <div class="flex justify-end space-x-3">
                        <button
                            class="px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg hover:bg-gray-200"
                            @click="$emit('update:modelValue', false)"
                        >
                            Annuler
                        </button>
                        <button
                            class="px-4 py-2 text-sm text-white bg-black rounded-lg hover:bg-red-700"
                            @click="$emit('confirm')"
                        >
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
defineProps({
    modelValue: Boolean,
});

defineEmits(["update:modelValue", "confirm"]);
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: all 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
