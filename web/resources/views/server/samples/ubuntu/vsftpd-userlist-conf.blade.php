@if (count($ftpAccounts) !== 0)
    
    @foreach($ftpAccounts as $ftpAccount)
        {{ $ftpAccount->ftp_username }}:{{ $ftpAccount->domain }}
    @endforeach         
        
@endif