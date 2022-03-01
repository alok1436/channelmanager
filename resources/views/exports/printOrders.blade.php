<table>
    <thead>
    <tr>
        <th>Quantity</th>
        <th>Item</th>
        <th>Hall</th>
        <th>Area</th>
        <th>Rack</th>
    </tr>
    </thead>
    <tbody>
        <?php $i=1; ?>
        @foreach($orders as $row)
        <tr>
            <td>{{ $row->quantity }}</td>
            <td>{{ $row->product ? $row->product->namelong : '' }}</td>
            <?php $legerstand = $row->product ? $row->product->lagerStand()->where('idwarehouse', $idwarehouse)->first() : '' ?>
            <td>{{ $legerstand ? $legerstand->hall : '' }}</td>
            <td>{{ $legerstand ? $legerstand->area : '' }}</td>
            <td>{{ $legerstand ? $legerstand->rack : '' }}</td>
        </tr>
            <?php $multi_orders = \App\Models\OrderItem::where('multiorder',$row->referenceorder)->get(); ?>
            @foreach($multi_orders as $multi_order)
            <tr>
                <td>{{ $multi_order->quantity }}</td>
                <td>{{ $multi_order->product ? $multi_order->product->namelong : '' }}</td>
                <?php $legerstand = $multi_order->product ? $multi_order->product->lagerStand()->where('idwarehouse', $idwarehouse)->first() : '' ?>
                <td>{{ $legerstand ? $legerstand->hall : '' }}</td>
                <td>{{ $legerstand ? $legerstand->area : '' }}</td>
                <td>{{ $legerstand ? $legerstand->rack : '' }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>