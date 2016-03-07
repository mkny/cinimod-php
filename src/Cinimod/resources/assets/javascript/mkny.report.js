/**
 * Report Object!
 * @param {object} object jQuery Object
 */

 function Report(object){
 	var that = this;

 	this.object=object;
 	this.mkny = new Mkny();


 }

 Report.prototype = {
 	setObject : function(object){
 		this.object = object;
 	},

	/**
	 * Datasource global
	 * 
	 * @param  {strin}   rel      Nome do relatorio para gerar
	 * @param  {Function} callback Tratamento dos dados
	 * @return {void}
	 */
	 datasource : function (rel, callback){
	 	$.ajax({
	 		url : '/admin/rel/get',
	 		type: 'get',
	 		data: {
	 			report : rel
	 		},
	 		dataType : 'json',
	 		success: function(ds){
	 			if(typeof callback == 'function'){
	 				callback(ds);
	 			} else {
	 				throw new Exception('Callback não definido!');
	 			}
	 		}
	 	});
	 },

	/**
	 * Funcao para gerar grafico tipo pizza
	 * 
	 * @param  {string} rel Nome do relatorio
	 * @return {void}
	 */
	 pie : function (rel){
	 	var e = this;
	 	google.charts.setOnLoadCallback(function(){
	 		e.datasource(rel, function(ds){
	 			var data = new google.visualization.DataTable(ds);
	 			var chart = new google.visualization.PieChart(e.object);
	 			chart.draw(data, {
					// width : 400,
					// height: 240
				});
	 		});
	 	});
	 },

	/**
	 * Funcao para gerar grafico tipo barras
	 * 
	 * @param  {string} rel Nome do relatorio
	 * @return {void}
	 */
	 bar : function (rel){
	 	var e = this;
	 	google.charts.setOnLoadCallback(function(){
	 		e.datasource(rel, function(ds){
	 			var data = new google.visualization.DataTable(ds);
	 			var chart = new google.visualization.BarChart(e.object);
	 			chart.draw(data, {
					// width : 400,
					// height: 240
				});
	 		});
	 	});
	 },

	/**
	 * Funcao para gerar grafico tipo linhas
	 * 
	 * @param  {string} rel Nome do relatorio
	 * @return {void}
	 */
	 line : function (rel){
	 	var e = this;
	 	google.charts.setOnLoadCallback(function(){
	 		e.datasource(rel, function(ds){
	 			var data = new google.visualization.DataTable(ds);
	 			var chart = new google.visualization.LineChart(e.object);
	 			chart.draw(data, {
					// width : 400,
					// height: 240
				});
	 		});
	 	});
	 },

	/**
	 * Funcao para gerar tabela dos dados informados (dataTable)
	 * 
	 * @param  {string} rel Nome do relatorio
	 * @return {void}
	 */
	 table : function(rel){
	 	var e = this;
	 	google.charts.setOnLoadCallback(function(){
	 		e.datasource(rel, function(ds){
	 			var data = new google.visualization.DataTable(ds);
	 			var chart = new google.visualization.Table(e.object);
				// data.setProperty('className', 'table');
				chart.draw(data, {
					alternatingRowStyle: true,
					cssClassNames: {
						headerRow: 'jump'
					}
				});
				// chart.JU.className = 'table';
				// chart.setProperty('className', 'table');
				// window.cc = chart;
			});
	 	});
	 },

	/**
	 * Funcao para gerar grafico tipo geo
	 * 
	 * @param  {string} rel Nome do relatorio
	 * @return {void}
	 */
	 geo: function (rel){
	 	var e = this;
	 	e.mkny.load('https://www.google.com/jsapi', function(){
	 		google.charts.setOnLoadCallback(function(){
	 			e.datasource(rel, function(ds){
	 				var data = new google.visualization.DataTable(ds);
	 				var chart = new google.visualization.GeoChart(e.object);
	 				chart.draw(data, {
	 					magnifyingGlass : {
	 						enable: true,
	 						zoomFactor: 12
	 					},
	 					region: 'BR',
	 					displayMode: 'markers'
	 				});
	 			});
	 		});
	 	});
	 }
	}

// Chart autoloader
$(function(){
	$('[data-chart]').each(function(){
		var e = $(this);
		var charttype = e.data('charttype') || 'pie';
		new Report(e.get(0))[charttype](e.data('chart'));

		// r(e.get(0), function(t){
		// 	t[charttype](e.data('chart'));
		// });
	});
});


/**
 * Usage:
 *
 * 		<div data-chart="report_questions_by_category" data-charttype="pie"></div>
 */