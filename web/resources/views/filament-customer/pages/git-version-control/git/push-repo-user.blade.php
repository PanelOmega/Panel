echo "Push started at {{ date('Y-m-d H:i:s') }}"

cd {{$projectDir}}

@if($privateKeyFile)
git config user.name {{$systemUsername}}
git config user.email  {{$systemEmail}}

git add .
git commit -m "{{$gitCommitInfo ?? 'Automated commit from deployment script at ' . date('Y-m-d H:i:s')}}"

git -c core.sshCommand="ssh -i {{$privateKeyFile}}" push || git -c core.sshCommand="ssh -i {{$privateKeyFile}}" push -u origin {{$branch}}

@else
echo "Make sure to add your SSH key to your repository and try again"
@endif

@if($deploymentScript)
    {!! $deploymentScript !!}
@endif

rm -rf {{$selfFile}}

