@extends('layouts.app')

@section('template_title')
    Embarques
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Embarques') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('embarques.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        
									<th >Numeconomico</th>
									<th >Entregado</th>
									<th >Desaduanado</th>
									<th >Clavenombre</th>
									<th >Tipooper</th>
									<th >Claveaduana</th>
									<th >Fechaembarque</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($embarques as $embarque)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
										<td >{{ $embarque->numEconomico }}</td>
										<td >{{ $embarque->entregado }}</td>
										<td >{{ $embarque->Desaduanado }}</td>
										<td >{{ $embarque->claveNombre }}</td>
										<td >{{ $embarque->tipoOper }}</td>
										<td >{{ $embarque->claveAduana }}</td>
										<td >{{ $embarque->fechaEmbarque }}</td>

                                            <td>
                                                <form action="{{ route('embarques.destroy', $embarque->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('embarques.show', $embarque->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('embarques.edit', $embarque->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $embarques->withQueryString()->links() !!}
            </div>
        </div>
    </div>
@endsection
