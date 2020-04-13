<div id="loadingSplash" class="container-fluid position-absolute vh-100 w-100 top-0 left-0 bg-light z-index-top">
    <div class="col-12 vh-100 w-100 text-center d-flex align-items-center">
        <h2 class="col-12 text-center">{{ __('Loading') }}&nbsp;<span id="loadingSplashDots" class="position-absolute">...</span></h2>
    </div>
</div>
<style nonce="{{ config('omen.cspToken') }}">
    #loadingSplash {
        z-index: 10;
    }

    #loadingSplashDots {
        display: inline-block;
        overflow: hidden;

        animation: dots 1s steps(5, end) infinite;
    }

    @keyframes dots {

        0%,
        20% {
            color: white;
            text-shadow: 0.25em 0 0 white, 0.5em 0 0 white;
        }

        40% {
            color: white;
            text-shadow: 0.25em 0 0 white, 0.5em 0 0 white;
        }

        60% {
            text-shadow: 0.25em 0 0 rgba(0, 0, 0, 0), 0.5em 0 0 white;
        }

        80%,
        100% {
            text-shadow: 0.25em 0 0 rgba(0, 0, 0, 0), 0.5em 0 0 rgba(0, 0, 0, 0);
        }
    }

</style>
