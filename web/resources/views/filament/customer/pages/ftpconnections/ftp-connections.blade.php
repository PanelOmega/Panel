<x-filament-panels::page>

    <div
        class="grid gap-4 gap-y-8 p-8 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        {{--        class="p-8 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">--}}

        <div class="mb-4">
            <p class="text-gray-600 dark:text-white">
                {{ $description['description_title'] }}
            </p>
        </div>

        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $description['title_current_connections'] }}
            </h2>
            <p class="text-gray-600 dark:text-white text-sm mt-1">
                {{ $description['description_reload'] }}
            </p>
            <button
                onclick="window.location.reload();"
                class="mt-4 px-4 py-2 bg-blue-500 dark:text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800 transition">
                Reload
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        User
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Logged in From
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Login Time
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Process ID
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                @foreach($ftpConnections as $connection)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $connection['user'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $connection['logged_in_from'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $connection['login_time'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $connection['status'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $connection['process_id'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <livewire:disconnect-ftp-connection :pid="$connection['process_id']"/>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
        <script>
            window.addEventListener('ftp-connection-disconnected', event => {
                const {type, message} = event.detail[0];

                if (type === 'success') {
                    alert(message);
                } else if (type === 'error') {
                    alert(message);
                }

                window.location.reload();
            });
        </script>
    @endpush
</x-filament-panels::page>
