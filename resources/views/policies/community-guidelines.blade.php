@extends('layouts.app')

@push('title')Community Guidelines | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col">
                <h1 class="text-center">Community Guidelines</h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                <h4>Overview</h4>
                <p>
                    The Parkour Hub website provides users with the ability to upload multimedia content such as images, videos
                    & text. These guidelines set the framework for what content is acceptable on the website as well as the
                    methods available for dealing with content that violates these guidelines. We want Parkour Hub to be a place
                    where anyone can find & share content and engage with other users in a positive manner. Parkour Hub values
                    freedom of speech and freedom of expression but we absolutely will take action against any illegal content,
                    NSFW content, spam & scams and targeted or repeated harassment or bullying. Action may include a
                    permanent account ban and/or assisting the police in a criminal investigation.
                </p>
                <h4>Be Respectful</h4>
                <p>
                    Parkour Hub values freedom of speech and expression. You may see content that you don't agree with. This
                    does not necessarily mean that it violates these guidelines. You must always respect the opinions of other
                    users.
                </p>
                <ul>
                    <li>Don't post content that is libelous, defamatory or threatening.</li>
                    <li>
                        Don't post content that is obscene, pornographic or contains nudity, excessive violence or gore.
                    </li>
                    <li>
                        Don't post content that exposes others' private information such as full name, phone number, email
                        address or mailing address.
                    </li>
                    <li>Don't post links to phishing or malware sites.</li>
                    <li>Don't impersonate other people or organisations.</li>
                    <li>Don't repeatedly post content of no/low value such as spamming a comment section.</li>
                    <li>Don't attack people based on whether you agree with them.</li>
                    <li>
                        You may challenge other users' opinions or beliefs as long as it is relevant and done in a respectful
                        and non-threatening manner.
                    </li>
                </ul>
                <h4>Be Relevant</h4>
                <p>
                    Content you upload should be relevant to the topic or subject. For example, posting a comment on a spot
                    should be relevant to that spot or to other comments on that spot.
                </p>
                <h4>Illegal Content</h4>
                <p>
                    Any content that is found to contain illegal material will immediately be permanently deleted and the user
                    permanently banned.
                </p>
                <h4>Copyright</h4>
                <p>
                    You must own the content that you upload or have explicit permission from the owner to use it. Content
                    that is found to infringe on copyright will immediately be hidden from the website until you update it.
                    Then an admin will review the updated version and if approved, the content will return to the site.
                </p>
                <h4>Promotion</h4>
                <p>
                    You may not promote other businesses, organisations, services or products that are not directly relevant
                    to the content they are associated with. For example, if you create an event, you could link to the official
                    website or social media of the event. If you wish to promote something that isn't directly related to
                    the content but is related to parkour, get in touch and we can try to reach an agreement.
                </p>
                <h4>Reporting Violations of These Guidelines</h4>
                <p>
                    Every piece of content has a report button that looks like this <i class="fa fa-flag"></i>. Clicking
                    this will report the content anonymously for an admin to review. An admin will take appropriate action
                    which may include, but is not limited to, asking the owner of the content to update it, permanently
                    deleting the content, permanently banning the user or leaving it as is if they don't see that it
                    violates any guidelines.
                </p>
                <h4>Updates to the Guidelines</h4>
                <p>Parkour Hub reserves the right to update these guidelines at any time.</p>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
