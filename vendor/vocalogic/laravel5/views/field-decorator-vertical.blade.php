<div class="row">
    <div class="{{ empty($span) ? '' : 'col-md-' . $span }}">
        <div class="form-group {{ empty($status) ? '' : 'has-' . $status }}">

            @if (in_array($type, ['raw', 'html', 'token', 'image', 'button', 'submit', 'reset']))
                {!! $label !!}{!! $input !!}
            @else
                {!! $label !!}
                @if (!empty($pre) || !empty($post))
                    <div class="input-group">
                        @endif
                        @if (!empty($pre))
                            <span class="input-group-addon">{{ $pre }}</span>
                        @endif
                        {!! $input !!}
                        @if (!empty($post))
                            <span class="input-group-addon">{{ $post }}</span>
                        @endif
                        @if (!empty($pre) || !empty($post))
                    </div>
                @endif
                @if (!empty($comment))
                    <p class="help-block">{{ $comment }}</p>
                @endif
        </div>
    </div>
    @endif
</div>