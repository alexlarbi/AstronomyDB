@extends('main')

@section('title', '- View cb')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <h1>Celestial Body {{ $cb->id }}</h1>
            <hr>

            <p class = "lead">{{ $cb->name }}</p>

            <p><b>Right Ascension:</b> {{ $cb->right_ascension }}</p>
            <p><b>Declination:</b> {{ $cb->declination }}</p>
            <p><b>Verification Status:</b>
                @if ($cb->verified == 1)
                    Verified
                @else
                    Not Verified
                @endif
            </p>
            @if (!empty($comet))
                <p>Comet's Speed: {{$comet->speed}}</p>
            @endif

            @if (!empty($galaxy))
                <p>Galaxy's Brightness: {{$galaxy->brightness}}</p>
                <p>Galaxy's Redshift: {{$galaxy->redshift}}</p>
                <p>Galaxy's Type: {{$galaxy->type}}</p>
            @endif

            @if (!empty($moon))
                <p>Moon's Orbital Period: {{$moon->orbital_period}}</p>
                <p>Moon's Radius: {{$moon->radius}}</p>
                <p>Moon's Planet Id: {{$moon->planet_id}}</p>
                <p>Planet's Orbital Period: {{$planetoid->orbital_period}}</p>
                <p>Planet's Type: {{$planetoid->planet_type}}</p>
            @endif

            @if (!empty($planet))
                <p><b>Planet's Orbital Period: </b>{{$planet->orbital_period}}</p>
                <p><b>Planet's Type: </b>{{$planet->planet_type}}</p>
            @endif

            @if (!empty($star))
                <p>Star's Spectral Brightness: {{$spectral->spectral_type}}</p>
                <p>Star's Brightness: {{$spectral->brightness}}</p>
            @endif
        </div>
        <div class="col-md-4">
            <div class="well">
                <div class="row">
                    <div class="col-sm-6">
                        {!! Html::linkRoute('cb.edit', 'Edit', array($cb->id), array('class' =>'btn btn-primary btn-block')) !!}
                    </div>
                    <div class="col-sm-6">
                        {!! Html::linkRoute('cb.destroy', 'Delete', array($cb->id), array('class' =>'btn btn-danger btn-block')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection