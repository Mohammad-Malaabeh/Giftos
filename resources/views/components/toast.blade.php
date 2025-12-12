{{-- Toast Notification Component --}}
<div x-data="toastManager()" @toast.window="addToast($event.detail)" class="fixed top-4 right-4 z-50 space-y-3"
    style="pointer-events: none;">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible" x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" :class="{
                'bg-green-50 border-green-200': toast.type === 'success',
                'bg-red-50 border-red-200': toast.type === 'error',
                'bg-blue-50 border-blue-200': toast.type === 'info',
                'bg-yellow-50 border-yellow-200': toast.type === 'warning'
             }" class="flex items-center gap-3 max-w-sm w-full border rounded-lg shadow-lg p-4 pointer-events-auto">

            {{-- Icon --}}
            <div class="flex-shrink-0">
                {{-- Success Icon --}}
                <svg x-show="toast.type === 'success'" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{-- Error Icon --}}
                <svg x-show="toast.type === 'error'" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{-- Info Icon --}}
                <svg x-show="toast.type === 'info'" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{-- Warning Icon --}}
                <svg x-show="toast.type === 'warning'" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            {{-- Message --}}
            <p x-text="toast.message" :class="{
                   'text-green-800': toast.type === 'success',
                   'text-red-800': toast.type === 'error',
                   'text-blue-800': toast.type === 'info',
                   'text-yellow-800': toast.type === 'warning'
               }" class="text-sm font-medium flex-1"></p>

            {{-- Close Button --}}
            <button @click="removeToast(toast.id)" type="button"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
    function toastManager() {
        return {
            toasts: [],
            nextId: 1,

            addToast(detail) {
                const id = this.nextId++;
                const toast = {
                    id: id,
                    message: detail.message || 'Success!',
                    type: detail.type || 'success',
                    visible: true
                };

                this.toasts.push(toast);

                // Auto-remove after 3 seconds
                setTimeout(() => {
                    this.removeToast(id);
                }, 3000);
            },

            removeToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index > -1) {
                    this.toasts[index].visible = false;
                    // Remove from array after transition
                    setTimeout(() => {
                        this.toasts.splice(index, 1);
                    }, 200);
                }
            }
        }
    }
</script>