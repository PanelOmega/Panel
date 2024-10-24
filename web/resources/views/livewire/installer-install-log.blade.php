<div>
    <div>
        <div id="js-install-log" wire:poll="pullLog" class="text-left text-sm font-medium text-gray-950 dark:text-primary-500 h-[20rem] overflow-x-hidden overflow-y-scroll">
            {!! $this->installLog !!}
        </div>
    </div>
    <script>
        window.setInterval(function() {
            var elem = document.getElementById('js-install-log');
            elem.scrollTop = elem.scrollHeight;
        }, 3000);
    </script>
</div>
