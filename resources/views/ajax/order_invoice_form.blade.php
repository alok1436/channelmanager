 <form action="editInvoiceData" method="POST">
    {{ csrf_field() }}
    <div class="modal-body">
        <input type="hidden" name="idorder" class="form-control" value="{{$row->idorder}}" id="form">
        <div class="form-group">
            <label for="">Customer</label>
            <input type="text" name="inv_customer" class="form-control" value="{{$row->inv_customer}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Customer Extra</label>
            <input type="text" name="inv_customerextra" class="form-control" value="{{$row->inv_customerextra}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Vat</label>
            <input type="text" name="inv_vat" class="form-control" value="{{$row->inv_vat}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Address1</label>
            <input type="text" name="inv_address1" class="form-control" value="{{$row->inv_address1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Address2</label>
            <input type="text" name="inv_address2" class="form-control" value="{{$row->inv_address2}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">PLZ</label>
            <input type="text" name="plz1" class="form-control" value="{{$row->plz1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">City</label>
            <input type="text" name="city1" class="form-control" value="{{$row->city1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Region</label>
            <input type="text" name="region1" class="form-control" value="{{$row->region1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Country</label>
            <select name="country1" class="form-control w-100">
            @foreach ($countries as $key => $country)
            <option value="{{$country->shortname}}"
            @if ($country->shortname==$row->country1)
            selected=""
            @endif
            >
            {{$country->shortname}}
            </option>
            @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="">Phone</label>
            <input type="text" name="telefon1" class="form-control" value="{{$row->telefon1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Fax</label>
            <input type="text" name="fax1" class="form-control" value="{{$row->fax1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Email</label>
            <input type="text" name="email1" class="form-control" value="{{$row->email1}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Invoice number</label>
            <input type="text" name="num_invoice" class="form-control" value="{{$row->num_invoice}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Vat customer</label>
            <input type="text" name="vat_customer" class="form-control" value="{{$row->vat_customer}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">cod univ</label>
            <input type="text" name="cod_univ" class="form-control" value="{{$row->cod_univ}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Pec</label>
            <input type="text" name="pec" class="form-control" value="{{$row->pec}}" data-id="{{$row->idorder}}">
        </div>
        <div class="form-group">
            <label for="">Invoice note</label>
            <input type="text" name="pec" class="form-control" value="{{$row->invoice_note}}" data-id="{{$row->idorder}}">
        </div>
    </div>
    <div class="modal-footer">
        <a href="createOrderInvoice?del={{$row->idorder}}&type=create_invoice" class="btn btn-danger" style="margin-right: 486px;">Create invoice</a>
        <button type="button" class="btn btn-default closeprintdoc" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Submit</button>
    </div>
    </form>