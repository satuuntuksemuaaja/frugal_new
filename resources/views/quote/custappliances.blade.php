@extends('layouts.locked', ['title' => 'Appliance Configuration | Enter Brand, Model and Size'])

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card groupBody">
            <div class="card-header bg-primary text-white"><b>Enter Appliance Information</b></div>

            <form role="form" action="{{ route('quote_customer_appliances_save', ['id' => $quote->id]) }}" method="post">
            {{ csrf_field() }}
            <div class="card-body">

              <div class="form-group form-row ">
                  <p>
                    <b>
                      Please enter the Brand, Model and Size of each of the appliances listed below.
                    </b>
                  </p>
              </div>

              <table class="table custApplianceTable table-striped mt-2" id="custApplianceTable">
                  <thead>
                  <th>Appliance</th>
                  <th>Brand</th>
                  <th>Model</th>
                  <th>Size</th>
                  </thead>
                  <tbody>@php echo $data; @endphp</tbody>
              </table>
            </div>

            <div class="card-footer" style="text-align:center;">
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Appliances</button>
            </div>
          </form>

        </div>
    </div>
</div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){

});

</script>
@endsection
