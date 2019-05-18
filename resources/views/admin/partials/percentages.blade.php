<div class="col-lg-6">
    <div class="card vendorBody">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title">Percentage Details</h4>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item">
                    <b>Frugal: </b>
                    <span class="float-right">
                        {{ $model->frugal_percentage }}% (${{ $model->frugal_cut }})
                    </span>
                </li>
                <li class="list-group-item">
                    <b>Designated Group: </b>
                    <span class="float-right">
                        {{ $model->percentage }}% (${{ $model->group_cut }})
                    </span>
                </li>
                <li class="list-group-item">
                    <b>2nd Designated Group: </b>
                    <span class="float-right">
                        {{ $model->second_group_percentage }}% (${{ $model->second_group_cut }})
                    </span>
                </li>
            </ul>
        </div>
    </div>
</div>
