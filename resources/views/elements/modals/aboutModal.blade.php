<div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <button type="button" class="close" data-dismiss="modal" aria-toggle="tooltip" title="{{ __('omen::Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-10 d-flex flex-row justify-content-center">
                        <img src="{{ asset(sprintf('%s/images/favicon/favicon-194x194.png', $assetPath)) }}" class="img-fluid" alt="{{ config('omen.title')}}">
                        <div><b>Omen</b><span>File</span><span>Manager</span></div>
                    </div>
                    <div class="col-10 mt-4 text-center">
                        <span class="mdi mdi-web mdi-24px"><a href="https://omen.jeremydev.ovh">{{ __('omen::Omen website') }}</a></span>
                    </div>
                    <div class="col-10 text-center">
                        <span class="mdi mdi-github mdi-24px"><a href="https://github.com/Kwaadpepper/omen">{{ __('omen::Omen source project') }}</a></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 p-4">
                        <div><span><b>Omen File Manager</b>&nbsp;<i>version&nbsp;:&nbsp;{{ config('omen.package.version') }}</i></span></div>
                        <div><span><b>Package</b>&nbsp;:&nbsp;{{ config('omen.package.name') }}</span></div>
                        <div><span><b>Copyright</b>&nbsp;:&nbsp;© Jérémy Munsch <a href="https://jeremydev.ovh">https://jeremydev.ovh</a></span></div>
                        <div class="mt-3">
                            <pre>{{ config('omen.package.description') }}</pre>
                        </div>
                        <div class="mt-3">
                            <pre id="aboutShortcuts"></pre>
                        </div>
                        <div class="mt-3">
                            <h6>Contributors</h6>
                            @foreach(config('omen.package.authors') as $author)
                            <pre>@foreach($author as $propertyName => $value){{ $value.PHP_EOL }}@endforeach</pre><br>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-1 bg-light">
                <button type="button" class="btn btn-dark" data-dismiss="modal" aria-label="{{ __('omen::Close') }}">{{ __('omen::Close') }}</button>
            </div>
        </div>
    </div>
</div>
