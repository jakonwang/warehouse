@extends('layouts.app')

@section('title', '盘点记录详情')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">盘点记录详情</h3>
        <div class="card-tools">
            <a href="{{ route('inventory-check.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> 返回列表
            </a>
            @if($inventoryCheckRecord->canConfirm())
                <form action="{{ route('inventory-check.confirm', $inventoryCheckRecord) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('确认要确认此盘点记录吗？')">
                        <i class="fas fa-check"></i> 确认盘点
                    </button>
                </form>
            @endif
            @if($inventoryCheckRecord->canDelete())
                <form action="{{ route('inventory-check.destroy', $inventoryCheckRecord) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('确认要删除此盘点记录吗？')">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">仓库</th>
                        <td>{{ $inventoryCheckRecord->store->name }}</td>
                    </tr>
                    <tr>
                        <th>状态</th>
                        <td>
                            @if($inventoryCheckRecord->status == 'pending')
                                <span class="badge badge-warning">待确认</span>
                            @else
                                <span class="badge badge-success">已确认</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>盘点人</th>
                        <td>{{ $inventoryCheckRecord->user->real_name }}</td>
                    </tr>
                    <tr>
                        <th>盘点时间</th>
                        <td>{{ $inventoryCheckRecord->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @if($inventoryCheckRecord->remark)
                    <tr>
                        <th>备注</th>
                        <td>{{ $inventoryCheckRecord->remark }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title">盘点明细</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>商品名称</th>
                                <th>系统库存</th>
                                <th>实际库存</th>
                                <th>差异</th>
                                <th>单位成本</th>
                                <th>差异成本</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryCheckRecord->inventoryCheckDetails as $detail)
                            <tr>
                                <td>{{ $detail->product->name }}</td>
                                <td>{{ $detail->system_quantity }}</td>
                                <td>{{ $detail->actual_quantity }}</td>
                                <td>{{ $detail->difference }}</td>
                                <td>{{ $detail->unit_cost }}</td>
                                <td>{{ $detail->total_cost }}</td>
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