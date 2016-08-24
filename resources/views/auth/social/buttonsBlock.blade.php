@foreach(config('erpnet-social-auth.socialLogin.availableProviders') as $provider)
    @if($provider!='laravel')
        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" name="provider_name" value="{{ $provider }}" class="btn btn-block btn-social btn-{{ $provider }}">
                    <span class="fa fa-{{ $provider }}"></span>
                    <i class="fa fa-btn {{ $type=='register'?'fa-user':'fa-sign-in' }}"></i>
                    {{ ucfirst($type) }} with {{ ucfirst($provider) }}
                </button>
            </div>
        </div>
    @endif
@endforeach
