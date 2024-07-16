<div class="flex gap-2 items-center justify-center m-auto smx-6 my-4 text-center text-sm text-gray-500">

    <div>
        PanelOmega v1.0.0 - Running on
    </div>

    @if($os == 'CloudLinux')
        <img src="{{asset('images/os/cloudlinux.svg')}}" class="inline-block w-6 h-6" alt="CloudLinux" title="CloudLinux">
    @endif

    @if($os == 'Ubuntu')
        <img src="{{asset('images/os/ubuntu.svg')}}" class="inline-block w-6 h-6" alt="Ubuntu" title="Ubuntu">
    @endif

    @if($os == 'Debian')
        <img src="{{asset('images/os/debian.svg')}}" class="inline-block w-6 h-6" alt="Debian" title="Debian">
    @endif

    <div>
        {{$os}}
    </div>
</div>
