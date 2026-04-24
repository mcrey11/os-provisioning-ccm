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
                        <th>{{ trans('view.contract.weborder_item_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webOrderItems as $webOrderItem)
                        <tr>
                            <td>
                                @if($webOrderItem->product)
                                    <a href="{{ route('WebOrderItem.edit', $webOrderItem->id) }}" title="{{ trans('view.contract.view_weborder_item') }}">
                                        {{ $webOrderItem->product->name }}
                                    </a>
                                @else
                                    <a href="{{ route('WebOrderItem.edit', $webOrderItem->id) }}" title="{{ trans('view.contract.view_weborder_item') }}">
                                        {{ $webOrderItem->name }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <span class="label label-{{ $webOrderItem->type === 'plan' ? 'primary' : ($webOrderItem->type === 'addon' ? 'success' : 'info') }}">
                                    {{ ucfirst($webOrderItem->type) }}
                                </span>
                            </td>
                            <td>{{ $webOrderItem->qty ?? 1 }}</td>
                            <td>
                                <form method="POST" action="{{ route('WebOrderItem.destroy', $webOrderItem->id) }}" style="display: inline-block;" onsubmit="return confirm('{{ trans('view.contract.delete_weborder_item_confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ trans('view.contract.delete_weborder_item') }}">
                                        <i class="fa fa-trash"></i> {{ trans('view.contract.delete') }}
                                    </button>
                                </form>
                            </td>
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
