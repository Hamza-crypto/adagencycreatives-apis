<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resume Download</title>
    <style>
        .mt-0 {
            margin-top: 0;
        }

        .mb-2 {
            margin-bottom: 0.25rem;
        }

        .creative-details p:first-child {
            margin: 5px 0;
        }

        .creative-details p:nth-child(2) {
            margin: 0;
            color: #7a8392;
        }

        .my_resume_eduarea .content {
            position: relative;
            padding-left: 40px;
        }

        #job-candidate-education.my_resume_eduarea .circle {
            background-color: rgba(211, 161, 31, .15);
            color: #D3A11F;
        }

        .my_resume_eduarea .circle {
            border-radius: 50%;
            -webkit-border-radius: 50%;
            -moz-border-radius: 50%;
            -ms-border-radius: 50%;
            -o-border-radius: 50%;
            background: rgba(217, 48, 37, 0.15);
            color: #D93025;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            left: 0;
            position: absolute;
            top: 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .my_resume_eduarea .top-info>* {
            display: inline-block;
            vertical-align: middle;
        }

        .my_resume_eduarea .edu_stats {
            font-size: 16px;
            margin: 0;
            line-height: 2.5em;
            font-family: var(--superio-heading-font), Arial, sans-serif;
            font-weight: 500;
            color: #202124;
        }

        #job-candidate-education.my_resume_eduarea .edu_center {
            color: #D3A11F;
        }

        .my_resume_eduarea .content:after {
            border-left: 2px dashed rgba(217, 48, 37, 0.15);
            content: "";
            height: calc(100% - 52px);
            width: 2px;
            left: 14px;
            position: absolute;
            top: 55px;
        }
    </style>

</head>

<body>
    <section style="display: flex; gap: 30px;">
        <div>
            <img src="{{ $data['profile_image'] }}" style="max-width: 200px; max-height: 200px;" />
        </div>
        <div>
            <h1 class="mb-2">{{ $data['name'] ?? '' }}</h1>
            <p class="mb-2 mt-0">{{ $data['title'] }}</p>
            <a href="tel:949-903-6732" class="mb-2" style="display: block;">{{ $data['phone_number'] ?? '' }}</a>
            <a href="mailto:{{ $user->email }}" class="mb-2">{{ $user->email }}</a>
        </div>
    </section>
    <section>
        <h2>About</h2>
        <p>{{ $data['about'] ?? '' }}</p>
    </section>
    @if ($user && $user->links)
        @php
            $websiteLink = $user->links->where('label', 'website')->first();

            if ($websiteLink && $websiteLink->url) {
                $url = $websiteLink->url;

                // Check if the URL doesn't start with 'http://' or 'https://'
    if (!preg_match('/^(http|https):\/\//', $url)) {
        $url = 'https://' . $url;
                }
            }
        @endphp

        @if (!empty($url))
            <section>
                <h2>Portfolio site</h2>
                <img src="{{ '/image.thum.io/get/' . $url }}" />
            </section>
        @endif
    @endif
    <section class="creative-details"
        style="display: grid; grid-template-columns: 1fr 1fr 1fr; grid-gap: 20px; margin-top: 15px;">
        <div>
            <p>Years of Experience</p>
            <p>{{ $data['years_of_experience'] ?? '' }}</p>
        </div>
        <div>
            <p>Email</p>
            <p><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
        </div>
        <div>
            <p>Phone Number</p>
            <p><a href="tel:949-903-6732">{{ $data['phone_number'] ?? '' }}</a></p>
        </div>
        <div>
            <p>Industry Experience</p>
            <p>
                @foreach ($data['industry_experience'] as $index => $ie)
                    {{ $ie }}@if (!$loop->last)
                        ,
                    @endif
                @endforeach

            </p>
        </div>
        <div>
            <p>Media Experience</p>
            @foreach ($data['media_experience'] as $index => $ie)
                {{ $ie }}@if (!$loop->last)
                    ,
                @endif
            @endforeach
        </div>
        <div>
            <p>Type of Work</p>
            <p> {{ $data['years_of_experience'] ?? '' }}</p>
        </div>
    </section>
    <section>
        <div id="job-candidate-education" class="candidate-detail-education my_resume_eduarea">
            <h2 class="title">Education</h2>
            @foreach ($educations as $education)
                <div class="content">
                    @if ($education->degree)
                        <div class="circle">{{ substr($education->degree, 0, 1) }}</div>
                    @endif

                    <div class="top-info"><span class="edu_stats">{{ $education->degree ?? '' }}</span></div>
                    <div class="edu_center"><span class="university">{{ $education->college ?? '' }}</span></div>
                </div>
            @endforeach

        </div>
    </section>
    <section>
        <div id="job-candidate-experience" class="candidate-detail-experience my_resume_eduarea color-blue">
            <h4 class="title">Work &amp; Experience</h4>

            @foreach ($experiences as $experience)
                <div class="content">
                    <div class="circle">
                        {{ substr($experience->title ?? ($experience->company ?? 'ABC'), 0, 1) }} </div>
                    <div class="top-info">
                        <span class="edu_stats">{{ $experience->title ?? '' }}</span>

                        <span class="year">
                            {{ $experience->started_at?->format('Y/m/d') ?? '' }} -
                            {{ $experience->completed_at?->format('Y/m/d') ?? '' }} </span>

                    </div>
                    <div class="edu_center">
                        <span class="university">{{ $experience->company ?? '' }}</span>
                    </div>
                    <p class="mb0">
                        {{ $experience->description ?? '' }}
                    </p>
                </div>
            @endforeach

        </div>
    </section>
    <section>
        <h2>Portfolio</h2>
        <div style="display: grid; gap: 5px;">
            @foreach ($portfolio_items as $item)
                <img src="{{ getAttachmentBasePath() . $item->path }}" style="max-width: 800px;" />
            @endforeach
        </div>
    </section>


    <script>
        window.print()
    </script>
</body>

</html>