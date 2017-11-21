var selected = null;

function SearchCoords(x, y){
	var coords = null;
	$.ajax({
		type: "POST",
        url: "../serverside/search_sector.php",
        data: "x=" + x + "&y=" + y,
        async: false,
       	contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(json){
        	coords = json;
        }
	});
	return coords;
}

function DrawPoint(ex, ey, color){
	color || (color = '#cd0a0a');
	$('canvas').drawArc({
		layer: true,
		groups: ['coords'],
		name: 'coordsArc',
		strokeStyle: color,
		strokeWidth: 1,
		x: ex, y: ey,
		radius: 10,
		start: 337.5, end: 382.5
	}).drawVector({
		layer: true,
		groups: ['coords'],
		name: 'coordsVectorLeft',
		strokeStyle: color,
		strokeWidth: 1,
		x: ex, y: ey,
		a1: 337.5, l1: 10
	}).drawVector({
		layer: true,
		groups: ['coords'],
		name: 'coordsVectorRight',
		strokeStyle: color,
		strokeWidth: 1,
		x: ex, y: ey,
		a1: 22.5, l1: 10
	});
}

function DrawRange(ex, ey){
	$('canvas').drawArc({
		layer: true,
		groups: ['ranges'],
		name: 'coordsRangeAttack',
		strokeStyle: '#cc0000',
		strokeWidth: 1,
		x: ex, y: ey,
		radius: 10
	}).drawArc({
		layer: true,
		groups: ['ranges'],
		name: 'coordsRangeMaxMovement',
		strokeStyle: '#4eb305',
		strokeWidth: 1,
		x: ex, y: ey,
		radius: 20
	}).drawArc({
		layer: true,
		groups: ['ranges'],
		name: 'coordsRangeMaxAttack',
		strokeStyle: '#0078ae',
		strokeWidth: 1,
		x: ex, y: ey,
		radius: 30
	})
}

function DrawSector(json){
	if(json.total){
		var c = $("canvas");
		var sa = json.angulo * 1;
        var ea = sa + 45;
    	if(json.total < 4){
    		c.drawLine({
    			layer: true,
        		groups: ['sectors'],
        		name: 'sectorLineLeft',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x1: parseInt(json.extremos[0].x), y1: parseInt(json.extremos[0].y),
        		x2: parseInt(json.extremos[1].x), y2: parseInt(json.extremos[1].y)
        	}).drawLine({
        		layer: true,
        		groups: ['sectors'],
        		name: 'sectorLineRight',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x1: parseInt(json.extremos[0].x), y1: parseInt(json.extremos[0].y),
        		x2: parseInt(json.extremos[2].x), y2: parseInt(json.extremos[2].y)
        	}).drawArc({
        		layer: true,
        		groups: ['sectors'],
        		name: 'sectorArcOuter',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x: 548, y: 548,
        		radius: parseInt(json.radio_exterior),
        		start: sa, end: ea
        	});
        }
        else{
            c.drawLine({
            	layer: true,
        		groups: ['sectors'],
        		name: 'sectorLineLeft',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x1: parseInt(json.extremos[2].x), y1: parseInt(json.extremos[2].y),
        		x2: parseInt(json.extremos[0].x), y2: parseInt(json.extremos[0].y)
        	}).drawLine({
        		layer: true,
        		groups: ['sectors'],
        		name: 'sectorLineRight',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x1: parseInt(json.extremos[3].x), y1: parseInt(json.extremos[3].y),
        		x2: parseInt(json.extremos[1].x), y2: parseInt(json.extremos[1].y)
        	}).drawArc({
        		layer: true,
        		groups: ['sectors'],
        		name: 'sectorArcIner',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x: 548, y: 548,
        		radius: parseInt(json.radio_interior),
        		start: sa, end: ea
        	}).drawArc({
        		layer: true,
        		groups: ['sectors'],
        		name: 'sectorArcOuter',
        		strokeStyle: '#4297d7',
        		strokeWidth: 1,
        		x: 548, y: 548,
        		radius: parseInt(json.radio_exterior),
        		start: sa, end: ea
        	});
        }
    }
}

$(function(){
	$('canvas').attr({
		width: 	1096,
		height:	1096
	}).mousemove(function(event){
		$("#coords_axis_x_current").val(Math.round(event.pageX - this.offsetLeft));
		$("#coords_axis_y_current").val(Math.round(event.pageY - this.offsetTop));
	}).mouseout(function(){
		$("#coords_axis_x_current").val(0);
		$("#coords_axis_y_current").val(0);
	}).click(function(event){
		$('canvas').removeLayerGroup('ranges').removeLayerGroup('coords').removeLayerGroup('sectors').drawLayers();
		var coords = SearchCoords(Math.round(event.pageX - this.offsetLeft), Math.round(event.pageY - this.offsetTop));
        if(coords){
        	DrawSector(coords);
        }
	}).drawArc({
		layer: true,
		groups: ['worlds'],
		name: 'worldArc6',
		strokeStyle: '#000',
		strokeWidth: 1,
		fillStyle: "#edfbd0",
		x: 548, y: 548,
		radius: 548
	}).drawArc({
		layer: true,
		groups: ['worlds'],
		name: 'worldArc5',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		radius: 340
	}).drawArc({
		layer: true,
		groups: ['worlds'],
		name: 'worldArc4',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		radius: 260
	}).drawArc({
		layer: true,
		groups: ['worlds'],
		name: 'worldArc3',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		radius: 195
	}).drawArc({
		layer: true,
		groups: ['worlds'],
		name: 'worldArc2',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		radius: 115
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector1',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 22.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector2',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 67.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector3',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 112.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector4',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 157.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector5',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 202.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector6',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 247.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector7',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 292.5, l1: 548
	}).drawVector({
		layer: true,
		groups: ['worlds'],
		name: 'worldVector8',
		strokeStyle: '#000',
		strokeWidth: 1,
		x: 548, y: 548,
		a1: 337.5, l1: 548
	});
	$("#search-coords").click(function(e){
		e.preventDefault();
		$('canvas').removeLayerGroup('ranges').removeLayerGroup('coords').removeLayerGroup('sectors').drawLayers();
		var ex = parseInt($("#coords_axis_x_one").val());
		var ey = parseInt($("#coords_axis_y_one").val());
		var coords = SearchCoords(ex, ey);
        if(coords){
        	DrawSector(coords);
        	DrawPoint(ex, ey);
        }
	});
	$("#show-range-coords").click(function(e){
		e.preventDefault();
		$('canvas').removeLayerGroup('ranges').drawLayers();
		DrawRange(parseInt($("#coords_axis_x_one").val()), parseInt($("#coords_axis_y_one").val()));
    });
	$("#calculate-coords").click(function(e){
		e.preventDefault();
		$('canvas').removeLayerGroup('ranges').removeLayerGroup('coords').removeLayerGroup('sectors').drawLayers();
		var ex1 = parseInt($("#coords_axis_x_one").val());
		var ey1 = parseInt($("#coords_axis_y_one").val());
		var ex2 = parseInt($("#coords_axis_x_two").val());
		var ey2 = parseInt($("#coords_axis_y_two").val());
		DrawPoint(ex1, ey1);
        DrawPoint(ex2, ey2, '#4297d7');
        $('canvas').drawLine({
    		layer: true,
    		groups: ['coords'],
    		name: 'coordsNexusLine',
    		strokeStyle: '#4297d7',
    		strokeWidth: 1,
    		x1: ex1, y1: ey1,
    		x2: ex2, y2: ey2
    	});
        var d = Math.ceil(Math.sqrt(Math.pow((ex1-ex2), 2) + Math.pow((ey1-ey2), 2)));
        alert("La distancia entre coordenadas es de " + d + " campos");
    });
	$("#search-commanders").autocomplete({
		source: "../serverside/search_commanders.php",
		minLength: 2,
		select: function productoSeleccionado(event, ui){
			event.preventDefault();
		    var nickname = ui.item.value;
		    var playerid = ui.item.id;
		    var html = "<li><i class='fa fa-times remove-searched' data-target='player-" + ui.item.id + "'></i><span class='texto-listado'>" + ui.item.value + "</span></li>";
		    $("#search-selected-players").append(html);
		    $("<input />").val(ui.item.id)
		    			  .attr({
		    				  id: "player-" + ui.item.id,
		    				  type: "hidden",
		    				  name: "players[]"
				    	  })
				    	  .appendTo("#buscar-coordenadas");
		    $("#search-commanders").val("");
		}
	});
	$("#search-alliances").autocomplete({
		source: "../serverside/search_alliances.php",
		minLength: 2,
		select: function productoSeleccionado(event, ui){
			event.preventDefault();
		    var nickname = ui.item.value;
		    var playerid = ui.item.id;
		    var html = "<li><i class='fa fa-times remove-searched' data-target='alliance-" + ui.item.id + "'></i><span class='texto-listado'>" + ui.item.value + "</span></li>";
		    $("#search-selected-alliances").append(html);
		    $("<input />").val(ui.item.id)
		    			  .attr({
		    				  id: "alliance-" + ui.item.id,
		    				  type: "hidden",
		    				  name: "alliances[]"
				    	  })
				    	  .appendTo("#buscar-coordenadas");
		    $("#search-alliances").val("");
		}
	});
	$(document).on("click", ".remove-searched", function() {
		var o = $(this);
		$("#" + o.data("target")).remove();
		o.parent().remove();
	});
});