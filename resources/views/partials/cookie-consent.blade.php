@if (!session('cookie_consent'))
    <div x-data="cookieBanner()" x-show="show" x-cloak class="fixed bottom-4 inset-x-0 z-50 flex justify-center px-4">
        <div
            class="bg-white border border-gray-200 shadow-lg rounded-xl p-4 max-w-3xl w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-700">
                We use cookies to improve your experience. By using our site, you agree to our cookie policy.
            </p>

            <button type="button" @click="acceptCookies"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Accept
            </button>
        </div>
    </div>

    <script>
        function cookieBanner() {
            return {
                show: true,
                acceptCookies() {
                    fetch('{{ route('cookies.accept') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({})
                        })
                        .then(() => {
                            this.show = false;
                        })
                        .catch(err => console.error(err));
                }
            }
        }
    </script>
@endif
