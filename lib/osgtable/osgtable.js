(function($){
    $.fn.extend({
        osgtable: function(options) {
            var defaults = {
                headers: [ 
                    {name:"color", type:"numeric"}, 
                    {name:"name",type:"string"}
                ],
                recs: [
                    //last column contains record id (so that header & record column matches index)
                    ["red", "apple", 0],
                    ["orange", "orange", 1],
                    ["yellow", "banana", 2]
                ],
                sort_column: 0,
                fetch_detail_callback: function(id, callback) {alert("clicked: "+id);},
                sort_up: true
            }
            var options = $.extend(defaults, options);
            var table = $(this);
            //var subrecords = new Array();

            //handler
            /*
            var mousemove = function(e) {
                if(options.type == "vertical") {
                    offset = bar.start.clientY - e.clientY;
                    options.resize(options.size + offset);
                } else {
                    //horizontal bar.. TODO
                }
            }
            */
            function render() {
                var html = "";
                html += "<table class=\"osgtable\">";

                //headers
                html += "<tr>";
                for(id in options.headers) {
                    var headers = options.headers[id];

                    var cls = headers.type;
                    if(id == options.sort_column) {
                        cls += " sorted";
                        if(options.sort_up) {
                            cls += " sort_up";
                        } else {
                            cls += " sort_down";
                        }
                    }
                    var style = "";
                    if(headers.width != null) {
                        style += "width: " + headers.width + "px;";
                    }
                    html += "<th class=\""+cls+"\" style=\""+style+"\">"+headers.name+"</th>";
                }
                html += "</tr>"; //end headers

                //records
                for(rid in options.recs) {
                    var rec = options.recs[rid];//skip id column
                    html += render_rec(rec, "record");
                }
                html += "</table>";
                return html;
            } 

            function render_rec(rec, cls) {
                var html = "";
                var rec_id = rec[options.headers.length];
                if(rec_id) {
                    html += "<tr name=\""+rec_id+"\" class=\""+cls+"\">";
                } else {
                    html += "<tr class=\""+cls+"\">";
                }
                for(cid in options.headers) {
                    var header = options.headers[cid];
                    var value = rec[cid];
                    if(value == null) {
                        value = "";
                    } else {
                        if(header.type == "email") {
                            value = "<a href=\"mailto:"+value+"\">"+value+"</a>";
                        } else if(header.type == "numeric") {
                            if(value == "99999" || value == "999999") {
                                value = "";
                            } 
                        }
                    }
                    if(cid == options.sort_column) {
                        html += "<td class=\"sorted\">"+value+"</td>";
                    } else {
                        html += "<td>"+value+"</td>";
                    }
                }
                html += "</tr>"; 
                return html;
            }

            function sort() {
                options.recs.sort(function(a_rec,b_rec) {
                    var a = a_rec[options.sort_column];
                    var b = b_rec[options.sort_column];
                    var header = options.headers[options.sort_column];
                    if(header.type == "numeric") {
                        //nothing special to do
                    } else if(header.type == "string") {
                        if(a != null) a = a.toLowerCase();
                        if(b != null) b = b.toLowerCase();
                    } else if(header.type == "email") {
                        //nothing special to do
                    }

                    if(a == null && b != null) return 1;
                    if(a != null && b == null) return -1;
                    if(options.sort_up) {
                        //swap value
                        var t = b;
                        b = a;
                        a = t;
                    }
                    if(a == b) return 0;
                    if(a < b) return 1;
                    return -1;
                    
              });
            }

            function refresh() {
                sort();
                table.html(render());

                //sorting 
                table.find("th").click(function() {
                    if(options.sort_column == $(this).index()) {
                        options.sort_up = !options.sort_up;
                    } else {
                        options.sort_column = $(this).index();
                        options.sort_up = true;
                    }
                    
                    refresh();
                });

                //expanding/collapsing record
                table.find("tr.record[name]").click(function() {
                    var rec_id = $(this).attr("name");
                    $(this).unbind('click');
                    $(this).removeClass("record");
                    $(this).addClass("record_expanded");
                    var tr = this;
                    options.fetch_detail(rec_id, function(data) {
                        if(data.status != "OK") {
                            alert(data.reason);
                        } else {
                            if(data.info != null) {
                                var buffer_td= "<td></td>";
                                if(options.sort_column == 0) {
                                    buffer_td = "<td class=\"sorted\"></td>";
                                }
                                var info_rec = $("<tr class='info'>"+buffer_td+"<td colspan='"+(options.headers.length-1)+"'>"+data.info+"</td></tr>");
                                //var info_rec = $("<tr class='info'><td colspan='"+(options.headers.length)+"'>"+data.info+"</td></tr>");
                                info_rec.insertAfter(tr);
                            }
                            if(data.subrecords != null) {
                                for(id in data.subrecords) {
                                    var rec = data.subrecords[id];
                                    var subrec =$(render_rec(rec, "subrecord"));
                                    subrec.insertAfter(tr);
                                }
                            }
                        }
                    });
                });

                /*
                bar.mousedown(function(e) {
                    bar.start = e;
                    document.onmousemove = mousemove;
                    return false;
                });
                bar.mouseup(function(e) {
                    document.onmousemove = null;
                    options.size = options.size + offset;
                    return false;
                });
                */
            }

            return this.each(function() {
                refresh();
            });

        }
    });
})(jQuery);

