<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Title:</strong>
	    @if (isset($feed))
	    {{ $feed }}
	    @endif
            {!! Form::text('title', session("title"), array('placeholder' => 'Title','class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Language:</strong>
            {!! Form::select('language', array('traditional' => '繁體', 'simplified' => '簡體'), isset($feed->language) ? $feed->language  : null, array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Category:</strong>
            @foreach ($cats as $cat)
	        @if (isset($cat_params) && in_array($cat->term_id, $cat_params))
                    <label>{!! Form::checkbox('category[]', $cat->term_id, true)!!} {!! $cat->name !!}</label>
	        @else
                    <label>{!! Form::checkbox('category[]', $cat->term_id, false)!!} {!! $cat->name !!}</label>
	        @endif
            @endforeach
        </div>
    </div>
    @if (isset($feed->uuid))
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>UUID:</strong>
                {!! Form::text('uuid', null, array('placeholder' => 'UUID','class' => 'form-control','readonly'=>'readonly')) !!}
            </div>
        </div>
    @else
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>UUID:</strong>
                {!! Form::text('uuid', null, array('placeholder' => 'UUID','class' => 'form-control')) !!}
            </div>
        </div>
    @endif
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Layout:</strong>
            {!! Form::select('layout', array('DEFAULT' => 'DEFAULT', 'YAHOO' => 'YAHOO', 'LINE' => 'LINE'), isset($feed->layout) ? $feed    ->layout  : null, array('class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Remark:</strong>
            {!! Form::textarea('remark', null, array('placeholder' => 'Remark','class' => 'form-control')) !!}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>

