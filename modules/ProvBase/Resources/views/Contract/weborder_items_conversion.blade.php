<?php
/**
 * WebOrderItems conversion panel within Item panel
 */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="fa fa-shopping-cart"></i>
            {{ trans('view.contract.weborder_items_conversion') }}
        </h3>
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>{{ trans('view.contract.weborder_items_info') }}</strong>
        </div>

        @if($webOrderItems && $webOrderItems->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('dt_header.web_order_item.product.name') }}</th>
                        <th>{{ trans('dt_header.web_order_item.type') }}</th>
                        <th>{{ trans('dt_header.web_order_item.qty') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webOrderItems as $webOrderItem)
                        <tr>
                            <td>
                                @if($webOrderItem->product)
                                    {{ $webOrderItem->product->name }}
                                @else
                                    {{ $webOrderItem->name }}
                                @endif
                            </td>
                            <td>
                                <span class="label label-{{ $webOrderItem->type === 'plan' ? 'primary' : ($webOrderItem->type === 'addon' ? 'success' : 'info') }}">
                                    {{ ucfirst($webOrderItem->type) }}
                                </span>
                            </td>
                            <td>{{ $webOrderItem->qty ?? 1 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('Contract.convertWebOrderItems', $contract->id) }}" class="form-horizontal">
                @csrf
                <div class="alert alert-warning">
                    <i class="fa fa-info-circle"></i>
                    <strong>{{ trans('view.contract.convert_info') }}</strong><br>
                    {{ trans('view.contract.convert_info_details') }}
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-check"></i>
                    {{ trans('view.contract.convert_weborder_items') }}
                </button>
            </form>
        @else
            <div class="alert alert-warning">
                {{ trans('view.contract.no_weborder_items') }}
            </div>
        @endif
    </div>
</div>

