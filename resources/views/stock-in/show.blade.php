@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">入库记录详情</h5>
                    <div>
                        <a href="{{ route('stock-ins.index') }}" class="btn btn-secondary">返回列表</a>
                        @if($stockInRecord->canDelete())
                            <form action="{{ route('stock-ins.destroy', $stockInRecord) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('确定要删除这条入库记录吗？')">删除</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>仓库：</strong>{{ $stockInRecord->store->name }}</p>
                            <p><strong>供应商：</strong>{{ $stockInRecord->supplier ?? '-' }}</p>
                            <p><strong>操作人：</strong>{{ $stockInRecord->user->real_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>总金额：</strong>¥{{ number_format($stockInRecord->total_amount, 2) }}</p>
                            <p><strong>总成本：</strong>¥{{ number_format($stockInRecord->total_cost, 2) }}</p>
                            <p><strong>创建时间：</strong>{{ $stockInRecord->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>

                    @if($stockInRecord->remark)
                        <div class="mb-4">
                            <h6>备注</h6>
                            <p>{{ $stockInRecord->remark }}</p>
                        </div>
                    @endif

                    @if($stockInRecord->image_path)
                        <div class="mb-4">
                            <h6>图片</h6>
                            <img src="{{ asset('storage/' . $stockInRecord->image_path) }}" alt="入库图片" class="img-fluid" style="max-height: 300px;">
                        </div>
                    @endif

                    <h6>入库明细</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>系列编码</th>
                                    <th>数量</th>
                                    <th>单价</th>
                                    <th>总金额</th>
                                    <th>总成本</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockInRecord->stockInDetails as $detail)
                                <tr>
                                    <td>{{ $detail->series_code }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>¥{{ number_format($detail->unit_price, 2) }}</td>
                                    <td>¥{{ number_format($detail->total_amount, 2) }}</td>
                                    <td>¥{{ number_format($detail->total_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 