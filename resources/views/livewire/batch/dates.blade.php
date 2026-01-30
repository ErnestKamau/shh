<div>
    @if(isset($batch->id))
        <div class="card border-0 mb-2" style="background-color: inherit !important">
            <div class="card-header- p-2 border-bottom" style="background-color: inherit !important">
                <h5 style="font-size: large"><i class="mdi mdi-calendar-month"></i> Batch Dates</h5>
            </div>
            <div class="card-body border-bottom bg-white">
                <div class="row no-gutters">
                    @foreach (getSampleDateTypes() as $date)
                        @if($date == 'Login Date' || $date == 'Target Date' || $date == 'Processing Date')
                        <div class="col-sm-4 p-1">
                            <b style="color: rgb(68, 68, 68);font-size:11px"><i class="mdi mdi-calendar-outline"></i> {{ $date }}</b> <br>
                            <span class=""
                                style="padding: 3px 9px; font-size:12px; border-radius: 15px; background-color: #f0f0f0; border: 1px solid #eeeeee; color:rgb(68, 68, 68)">{{ $batch->get_date($date) ? date('Y-m-d', strtotime($batch->get_date($date)['date'])) : '-' }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
