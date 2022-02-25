<table>
    <thead>
    <tr>
        <th>Sr No.</th>
        <th>Model Number</th>
        <th>Article</th>
        @foreach ($warehouses as $key => $w)
        <th>{{$w->shortname}}</th>
        <th>Hall</th>
        <th>Area</th>
        <th>Rack</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
        <?php $i=1; ?>
        @foreach($products as $row)
        <tr>
            <td>{{ $row->sort }}</td>
            <td>{{ $row->modelcode }}</td>
            <td>{{ $row->nameshort }}</td>
            @foreach ($warehouses as $key => $w)
            <?php $record = $row->lagerStand()->where('idwarehouse', $w->idwarehouse)->first(); ?>
            <td><?php echo $record  ? $record->quantity : 0; ?></td>
            <td><?php echo $record  ? $record->hall : ''; ?></td>
            <td><?php echo $record  ? $record->area : ''; ?></td>
            <td><?php echo $record  ? $record->rack : ''; ?></td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>