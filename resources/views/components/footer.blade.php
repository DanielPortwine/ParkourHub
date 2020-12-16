<div id="footer" class="py-3 section grey-section position-absolute w-100">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h3 class="footer-subtitle sedgwick">Resources</h3>
                <ul class="footer-links">
                    <li class="footer-link"><a href="">Support</a></li>
                    <li class="footer-link"><a href="">Contact</a></li>
                    <li class="footer-link"><a href="">Privacy & Terms</a></li>
                </ul>
            </div>
            <div class="col-md-8">
                <h3 class="footer-subtitle mb-0 sedgwick">Subscribe</h3>
                <small class="mb-2">Receive updates when new features are added or improved.</small>
                <form method="POST" action="/subscribe">
                    @csrf
                    <div class="row">
                        <div class="col col-md-10 pr-0">
                            <input type="email" name="email" placeholder="Email Address" class="w-100 @error('email') is-invalid @enderror">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-auto col-md-2">
                            <input type="submit" value="Subscribe" class="btn btn-green">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-center pt-3 mb-0">Copyright &#169; Parkour Hub</p>
    </div>
</div>
