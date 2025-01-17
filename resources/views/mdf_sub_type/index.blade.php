@extends('layouts.admin')

@section('title')
    {{__('Manage MDF Sub Type')}}
@endsection

@section('action-button')
    <div class="row align-items-center m-1">
        @can('Create MDF Sub Type')
            <div class="col-auto pe-0">
                <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create MDF Sub Type')}}" data-ajax-popup="true" data-size="md" data-title="{{__('Create MDF Sub Type')}}" data-url="{{route('mdf_sub_type.create')}}"><i class="ti ti-plus text-white"></i></a>
            </div>
        @endcan
    </div>
    @endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{__('Setup')}}</li>
    <li class="breadcrumb-item active" aria-current="page">{{__('MDF Sub Type')}}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card table-card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{__('Type')}}</th>
                                    <th>{{__('Sub Type')}}</th>
                                    <th width="250px">{{__('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                <tr>
                                    <td>{{ $type->type->name }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td class="Action">
                                        <span>
                                        @can('Edit MDF Sub Type')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" data-size="md" data-url="{{ route('mdf_sub_type.edit',$type->id) }}" data-ajax-popup="true" data-title="{{__('Edit MDF Sub Type')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Edit MDF Sub Type')}}" ><i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                            @endcan
                                            {{-- @if(count($types) > 1) --}}
                                                @can('Delete MDF Sub Type')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['mdf_sub_type.destroy', $type->id]]) !!}
                                                            <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Delete')}}">
                                                               <span class="text-white"> <i class="ti ti-trash"></i></span></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            {{-- @endif --}}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
