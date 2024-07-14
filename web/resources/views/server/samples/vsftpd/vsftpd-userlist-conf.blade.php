@if (count($ftpAccounts) !== 0)

@foreach($ftpAccounts as $ftpAccount)
{{ $ftpAccount->ftp_username_prefix }}{{ $ftpAccount->ftp_username }}
@endforeach

@endif
