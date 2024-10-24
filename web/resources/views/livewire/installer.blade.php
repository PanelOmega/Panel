<div>

    <div class="flex flex-col justify-center items-center h-screen text-center">

        <div class="flex flex-col justify-center items-center text-center">
            <img src="{{asset('images/logo/omega.svg')}}" class="h-12 mb-4 dark:hidden" alt="PanelOmega Logo">
            <img src="{{asset('images/logo/omega-dark.svg')}}" class="h-12 mb-4 hidden dark:block" alt="PanelOmega Logo">
        </div>

        <div class="text-left w-[50rem] mt-8">
            {{$this->form}}
        </div>

    </div>

</div>
