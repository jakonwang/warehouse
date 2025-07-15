@extends('layouts.app')

@section('title', '库存盘点历史')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">库存盘点历史</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.check') }}" class="btn btn-primary">进行盘点</a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.check-history') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="check_date">盘点日期</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="check_date" name="check_date" 
                                               value="{{ $checkDate }}">
                                        <button class="btn btn-outline-secondary" type="submit">查询</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>盘点日期</th>
                                    <th>系列代码</th>
                                    <th>系统库存</th>
                                    <th>实际库存</th>
                                    <th>差异数量</th>
                                    <th>单价</th>
                                    <th>差异金额</th>
                                    <th>盘点人</th>
                                    <th>备注</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                <tr>
                                    <td>{{ $record->check_date ? date('Y-m-d', strtotime($record->check_date)) : '未知' }}</td>
                                    <td>{{ $record->inventory->series_code }}</td>
                                    <td>{{ $record->system_quantity }}</td>
                                    <td>{{ $record->actual_quantity }}</td>
                                    <td class="{{ $record->difference > 0 ? 'text-success' : ($record->difference < 0 ? 'text-danger' : '') }}">
                                        {{ $record->difference }}
                                    </td>
                                    <td>{{ number_format($record->unit_price, 2) }}</td>
                                    <td class="{{ $record->difference_amount > 0 ? 'text-success' : ($record->difference_amount < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($record->difference_amount, 2) }}
                                    </td>
                                    <td>{{ $record->creator->name }}</td>
                                    <td>{{ $record->note }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $records->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    border-radius: 1rem;
}

.card-header {
    background: none;
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 500;
    color: var(--text-secondary);
    border-bottom-width: 1px;
}

.table td {
    vertical-align: middle;
}

.text-danger {
    color: var(--danger) !important;
}

.text-success {
    color: var(--success) !important;
}
</style>
@endsection 