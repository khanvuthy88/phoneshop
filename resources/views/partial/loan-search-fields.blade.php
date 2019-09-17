@if (isAdmin())
    {{-- Branch --}}
    <div class="col-sm-6 col-lg-2 pl-1 pr-0">
        <select name="branch" id="branch" class="form-control select2">
            <option value="">{{ trans('app.branch') }}</option>
            @foreach (allBranches() as $branch)
                <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->location }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Agent --}}
    <div class="col-sm-6 col-lg-2 pl-1 pr-0">
        <select name="agent" id="agent" class="form-control select2">
            <option value="">{{ trans('app.agent') }}</option>
            @foreach ($agents as $agent)
                <option value="{{ $agent->id }}" {{ request('agent') == $agent->id ? 'selected' : '' }}>
                    {{ $agent->name }}
                </option>
            @endforeach
        </select>
    </div>
@else
    <div class="col-lg-4"></div>
@endif

{{-- Text search --}}
<div class="col-lg-4 pl-1">
    @include('partial.search-input-group')
</div>
