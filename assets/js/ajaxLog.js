function fireRequest(e, t, n) {
    return $.ajax({type: "POST", url: e, beforeSend: function() {
            if (typeof t.before !== "undefined" && typeof t.divid !== "undefined") {
                $("#".concat(t.divid)).children("ol").append("<li id=\"ajaxNow\">".concat(t.before.concat(n)).concat("</li>"))
                $("#".concat(t.divid)).scrollTop($("#".concat(t.divid)).prop("scrollHeight"))
            } else if (typeof t.divid !== "undefined") {
                $("#".concat(t.divid)).append(n)
            }
        }, error: function() {
            if (typeof t.before !== "undefined" && typeof t.divid !== "undefined") {
                $("#ajaxNow").remove()
                $("#".concat(t.divid)).children("ol").append("<li>".concat(t.before).concat("</li>"))
                $("#".concat(t.divid)).append("Error ".concat(t.before))
                $("#".concat(t.divid)).scrollTop($("#".concat(t.divid)).prop("scrollHeight"))
            } else if (typeof t.divid !== "undefined") {
                $("#".concat(t.divid)).append("Error ".concat(e))
            }
        }, success: function($response) {
            if (typeof t.after !== "undefined" && typeof t.divid !== "undefined") {
                $("#ajaxNow").remove()
                $("#".concat(t.divid)).children("ol").append("<li>".concat(t.before).concat("</li>"))
                $("#".concat(t.divid)).children("ol").append(("<li>".concat(t.after)).concat("</li>"))
                $("#".concat(t.divid)).scrollTop($("#".concat(t.divid)).prop("scrollHeight"))
            } else if (typeof t.divid !== "undefined") {
                $("#".concat(t.divid)).append("Success ".concat(e))
            }
            if (typeof t.printr !== "undefined" && typeof t.divid !== "undefined" && t.printr === "yes") {
                $("#".concat(t.divid)).html($response);
            }
            if (typeof t.hidingdiv !== "undefined" && typeof t.hidingdivid !== "undefined" && t.hidingdiv === "yes") {
                $("#".concat(t.hidingdivid)).hide();
            }
        }})
}
function sequenceRequest(e, t, n) {
    initurl = e[0];
    initscrn = t[0];
    e.splice(0, 1);
    t.splice(0, 1);
    startingpoint = fireRequest(initurl, initscrn, n);
    $.each(e, function(e, r) {
        startingpoint = startingpoint.pipe(function(i, s, o) {
            console.log("Sequence " + e + " is " + s);
            return fireRequest(r, t[e], n)
        }, function(t, n, r) {
            console.log("Sequence " + e + " is " + n + " with response " + r)
        })
    })
}