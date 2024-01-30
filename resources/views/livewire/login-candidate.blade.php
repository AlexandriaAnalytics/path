<div class="min-h-screen flex items-center justify-center bg-gray-900 py-12 px-4 sm:px-6 lg:px-8" style="background-color: rgb(249 250 251);">
    <div class="p-4 card shadow rounded max-w-md w-full space-y-8 bg-white shadow-md overflow-hidden sm:rounded-lg p-6">
        <header class="mb-4">
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Login Candidate 🧑‍🎓
            </h1>
        </header>
        <main >
            <form wire:submit.prevent="handleLoginCandidate" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="id" class="sr-only">Email</label>
                        <input type="number" wire:model="id" id="id" placeholder="write you own candidate number" class="rounded appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                        @error('id') <span class="error text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <button type="submit" class="w-full p-2 text-white rounded" style="background-color: rgb(14 165 233);">
                        Login
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>