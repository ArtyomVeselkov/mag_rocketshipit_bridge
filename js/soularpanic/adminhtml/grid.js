var soularpanicGridMassaction = Class.create(foomanGridMassaction || varienGridMassaction, {
    apply: function($super) {
	var shippingOverrides = [];
	$('sales_order_grid_table').getElementsBySelector('.soularpanic_shippingmethod').each(function(s) {
	    var id = s.readAttribute('rel');
	    var optionElt = s.getElementsBySelector('option').find(
		function(elt) { return elt.selected });
	    shippingOverrides.push(id + '|' 
				   + s.value + '|' 
				   + optionElt.readAttribute('data-methodName') + '|'
				   + optionElt.readAttribute('data-methodPrice'));
	});
	new Insertion.Bottom(this.formAdditional,
			     this.fieldTemplate.evaluate({
				 name: 'shipping_override', 
				 value: shippingOverrides }));
	return $super();
    }
});

var soularpanicGrid = Class.create(foomanGrid || varienGrid, {});
