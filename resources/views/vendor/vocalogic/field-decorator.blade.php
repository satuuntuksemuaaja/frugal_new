@if (in_array($type, ['raw', 'html', 'token', 'image', 'button', 'submit', 'reset']))
  {!! $label !!}{!! $input !!}
@else
  <div class="form-group form-row {{ empty($status) ? '' : 'has-' . $status }}">
    {!! $label !!}
    <div class="{{ empty($span) ? '' : 'col-md-' . $span }}">
      @if (!empty($pre) || !empty($post))
        <div class="input-group input-group--inline">
          @endif
          @if (!empty($pre))
            <span class="input-group-addon">{!! $pre !!}</span>
          @endif
          {!! $input !!}
          @if (!empty($post))
            <span class="input-group-addon">{!! $post !!}</span>
          @endif
          @if (!empty($pre) || !empty($post))
        </div>
      @endif
      @if (!empty($comment))
        <p class="help-block mt-1 text-muted" style="font-size: 12px;">{!! $comment !!}</p>
      @endif
    </div>
  </div>
@endif