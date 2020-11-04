(function () {
    function B(I, A, N) {
        var M = B.resolve(I);
        if (null == M) {
            N = N || I;
            A = A || "root";
            var K = new Error('Failed to require "' + N + '" from "' + A + '"');
            K.path = N;
            K.parent = A;
            K.require = true;
            throw K
        }
        var J = B.modules[M];
        if (!J._resolving && !J.exports) {
            var L = {};
            L.exports = {};
            L.client = L.component = true;
            J._resolving = true;
            J.call(this, L.exports, B.relative(M), L);
            delete J._resolving;
            J.exports = L.exports
        }
        return J.exports
    }

    B.modules = {};
    B.aliases = {};
    B.resolve = function (A) {
        if (A.charAt(0) === "/") {
            A = A.slice(1)
        }
        var F = [A, A + ".js", A + ".json", A + "/index.js", A + "/index.json"];
        for (var E = 0; E < F.length; E++) {
            var A = F[E];
            if (B.modules.hasOwnProperty(A)) {
                return A
            }
            if (B.aliases.hasOwnProperty(A)) {
                return B.aliases[A]
            }
        }
    };
    B.normalize = function (A, F) {
        var G = [];
        if ("." != F.charAt(0)) {
            return F
        }
        A = A.split("/");
        F = F.split("/");
        for (var H = 0; H < F.length; ++H) {
            if (".." == F[H]) {
                A.pop()
            } else {
                if ("." != F[H] && "" != F[H]) {
                    G.push(F[H])
                }
            }
        }
        return A.concat(G).join("/")
    };
    B.register = function (D, A) {
        B.modules[D] = A
    };
    B.alias = function (A, D) {
        if (!B.modules.hasOwnProperty(A)) {
            throw new Error('Failed to alias "' + A + '", it does not exist')
        }
        B.aliases[D] = A
    };
    B.relative = function (F) {
        var G = B.normalize(F, "..");

        function A(D, E) {
            var C = D.length;
            while (C--) {
                if (D[C] === E) {
                    return C
                }
            }
            return -1
        }

        function H(D) {
            var C = H.resolve(D);
            return B(C, F, D)
        }

        H.resolve = function (E) {
            var D = E.charAt(0);
            if ("/" == D) {
                return E.slice(1)
            }
            if ("." == D) {
                return B.normalize(G, E)
            }
            var C = F.split("/");
            var J = A(C, "deps") + 1;
            if (!J) {
                J = 0
            }
            E = C.slice(0, J + 1).join("/") + "/deps/" + E;
            return E
        };
        H.exists = function (C) {
            return B.modules.hasOwnProperty(H.resolve(C))
        };
        return H
    };
    B.register("abpetkov-transitionize/transitionize.js", function (F, H, A) {
        A.exports = G;
        function G(D, C) {
            if (!(this instanceof G)) {
                return new G(D, C)
            }
            this.element = D;
            this.props = C || {};
            this.init()
        }

        G.prototype.isSafari = function () {
            return /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor)
        };
        G.prototype.init = function () {
            var D = [];
            for (var C in this.props) {
                D.push(C + " " + this.props[C])
            }
            this.element.style.transition = D.join(", ");
            if (this.isSafari()) {
                this.element.style.webkitTransition = D.join(", ")
            }
        }
    });
    B.register("ftlabs-fastclick/lib/fastclick.js", function (F, G, H) {
        function A(D) {
            var E, C = this;
            this.trackingClick = false;
            this.trackingClickStart = 0;
            this.targetElement = null;
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.lastTouchIdentifier = 0;
            this.touchBoundary = 10;
            this.layer = D;
            if (!D || !D.nodeType) {
                throw new TypeError("Layer must be a document node")
            }
            this.onClick = function () {
                return A.prototype.onClick.apply(C, arguments)
            };
            this.onMouse = function () {
                return A.prototype.onMouse.apply(C, arguments)
            };
            this.onTouchStart = function () {
                return A.prototype.onTouchStart.apply(C, arguments)
            };
            this.onTouchMove = function () {
                return A.prototype.onTouchMove.apply(C, arguments)
            };
            this.onTouchEnd = function () {
                return A.prototype.onTouchEnd.apply(C, arguments)
            };
            this.onTouchCancel = function () {
                return A.prototype.onTouchCancel.apply(C, arguments)
            };
            if (A.notNeeded(D)) {
                return
            }
            if (this.deviceIsAndroid) {
                D.addEventListener("mouseover", this.onMouse, true);
                D.addEventListener("mousedown", this.onMouse, true);
                D.addEventListener("mouseup", this.onMouse, true)
            }
            D.addEventListener("click", this.onClick, true);
            D.addEventListener("touchstart", this.onTouchStart, false);
            D.addEventListener("touchmove", this.onTouchMove, false);
            D.addEventListener("touchend", this.onTouchEnd, false);
            D.addEventListener("touchcancel", this.onTouchCancel, false);
            if (!Event.prototype.stopImmediatePropagation) {
                D.removeEventListener = function (N, M, P) {
                    var O = Node.prototype.removeEventListener;
                    if (N === "click") {
                        O.call(D, N, M.hijacked || M, P)
                    } else {
                        O.call(D, N, M, P)
                    }
                };
                D.addEventListener = function (N, O, P) {
                    var M = Node.prototype.addEventListener;
                    if (N === "click") {
                        M.call(D, N, O.hijacked || (O.hijacked = function (I) {
                                if (!I.propagationStopped) {
                                    O(I)
                                }
                            }), P)
                    } else {
                        M.call(D, N, O, P)
                    }
                }
            }
            if (typeof D.onclick === "function") {
                E = D.onclick;
                D.addEventListener("click", function (J) {
                    E(J)
                }, false);
                D.onclick = null
            }
        }

        A.prototype.deviceIsAndroid = navigator.userAgent.indexOf("Android") > 0;
        A.prototype.deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
        A.prototype.deviceIsIOS4 = A.prototype.deviceIsIOS && /OS 4_\d(_\d)?/.test(navigator.userAgent);
        A.prototype.deviceIsIOSWithBadTarget = A.prototype.deviceIsIOS && /OS ([6-9]|\d{2})_\d/.test(navigator.userAgent);
        A.prototype.needsClick = function (C) {
            switch (C.nodeName.toLowerCase()) {
                case"button":
                case"select":
                case"textarea":
                    if (C.disabled) {
                        return true
                    }
                    break;
                case"input":
                    if (this.deviceIsIOS && C.type === "file" || C.disabled) {
                        return true
                    }
                    break;
                case"label":
                case"video":
                    return true
            }
            return /\bneedsclick\b/.test(C.className)
        };
        A.prototype.needsFocus = function (C) {
            switch (C.nodeName.toLowerCase()) {
                case"textarea":
                    return true;
                case"select":
                    return !this.deviceIsAndroid;
                case"input":
                    switch (C.type) {
                        case"button":
                        case"checkbox":
                        case"file":
                        case"image":
                        case"radio":
                        case"submit":
                            return false
                    }
                    return !C.disabled && !C.readOnly;
                default:
                    return /\bneedsfocus\b/.test(C.className)
            }
        };
        A.prototype.sendClick = function (E, D) {
            var J, C;
            if (document.activeElement && document.activeElement !== E) {
                document.activeElement.blur()
            }
            C = D.changedTouches[0];
            J = document.createEvent("MouseEvents");
            J.initMouseEvent(this.determineEventType(E), true, true, window, 1, C.screenX, C.screenY, C.clientX, C.clientY, false, false, false, false, 0, null);
            J.forwardedTouchEvent = true;
            E.dispatchEvent(J)
        };
        A.prototype.determineEventType = function (C) {
            if (this.deviceIsAndroid && C.tagName.toLowerCase() === "select") {
                return "mousedown"
            }
            return "click"
        };
        A.prototype.focus = function (D) {
            var C;
            if (this.deviceIsIOS && D.setSelectionRange && D.type.indexOf("date") !== 0 && D.type !== "time") {
                C = D.value.length;
                D.setSelectionRange(C, C)
            } else {
                D.focus()
            }
        };
        A.prototype.updateScrollParent = function (D) {
            var C, E;
            C = D.fastClickScrollParent;
            if (!C || !C.contains(D)) {
                E = D;
                do {
                    if (E.scrollHeight > E.offsetHeight) {
                        C = E;
                        D.fastClickScrollParent = E;
                        break
                    }
                    E = E.parentElement
                } while (E)
            }
            if (C) {
                C.fastClickLastScrollTop = C.scrollTop
            }
        };
        A.prototype.getTargetElementFromEventTarget = function (C) {
            if (C.nodeType === Node.TEXT_NODE) {
                return C.parentNode
            }
            return C
        };
        A.prototype.onTouchStart = function (D) {
            var C, E, J;
            if (D.targetTouches.length > 1) {
                return true
            }
            C = this.getTargetElementFromEventTarget(D.target);
            E = D.targetTouches[0];
            if (this.deviceIsIOS) {
                J = window.getSelection();
                if (J.rangeCount && !J.isCollapsed) {
                    return true
                }
                if (!this.deviceIsIOS4) {
                    if (E.identifier === this.lastTouchIdentifier) {
                        D.preventDefault();
                        return false
                    }
                    this.lastTouchIdentifier = E.identifier;
                    this.updateScrollParent(C)
                }
            }
            this.trackingClick = true;
            this.trackingClickStart = D.timeStamp;
            this.targetElement = C;
            this.touchStartX = E.pageX;
            this.touchStartY = E.pageY;
            if (D.timeStamp - this.lastClickTime < 200) {
                D.preventDefault()
            }
            return true
        };
        A.prototype.touchHasMoved = function (D) {
            var C = D.changedTouches[0], E = this.touchBoundary;
            if (Math.abs(C.pageX - this.touchStartX) > E || Math.abs(C.pageY - this.touchStartY) > E) {
                return true
            }
            return false
        };
        A.prototype.onTouchMove = function (C) {
            if (!this.trackingClick) {
                return true
            }
            if (this.targetElement !== this.getTargetElementFromEventTarget(C.target) || this.touchHasMoved(C)) {
                this.trackingClick = false;
                this.targetElement = null
            }
            return true
        };
        A.prototype.findControl = function (C) {
            if (C.control !== undefined) {
                return C.control
            }
            if (C.htmlFor) {
                return document.getElementById(C.htmlFor)
            }
            return C.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")
        };
        A.prototype.onTouchEnd = function (P) {
            var D, C, E, M, N, O = this.targetElement;
            if (!this.trackingClick) {
                return true
            }
            if (P.timeStamp - this.lastClickTime < 200) {
                this.cancelNextClick = true;
                return true
            }
            this.cancelNextClick = false;
            this.lastClickTime = P.timeStamp;
            C = this.trackingClickStart;
            this.trackingClick = false;
            this.trackingClickStart = 0;
            if (this.deviceIsIOSWithBadTarget) {
                N = P.changedTouches[0];
                O = document.elementFromPoint(N.pageX - window.pageXOffset, N.pageY - window.pageYOffset) || O;
                O.fastClickScrollParent = this.targetElement.fastClickScrollParent
            }
            E = O.tagName.toLowerCase();
            if (E === "label") {
                D = this.findControl(O);
                if (D) {
                    this.focus(O);
                    if (this.deviceIsAndroid) {
                        return false
                    }
                    O = D
                }
            } else {
                if (this.needsFocus(O)) {
                    if (P.timeStamp - C > 100 || this.deviceIsIOS && window.top !== window && E === "input") {
                        this.targetElement = null;
                        return false
                    }
                    this.focus(O);
                    if (!this.deviceIsIOS4 || E !== "select") {
                        this.targetElement = null;
                        P.preventDefault()
                    }
                    return false
                }
            }
            if (this.deviceIsIOS && !this.deviceIsIOS4) {
                M = O.fastClickScrollParent;
                if (M && M.fastClickLastScrollTop !== M.scrollTop) {
                    return true
                }
            }
            if (!this.needsClick(O)) {
                P.preventDefault();
                this.sendClick(O, P)
            }
            return false
        };
        A.prototype.onTouchCancel = function () {
            this.trackingClick = false;
            this.targetElement = null
        };
        A.prototype.onMouse = function (C) {
            if (!this.targetElement) {
                return true
            }
            if (C.forwardedTouchEvent) {
                return true
            }
            if (!C.cancelable) {
                return true
            }
            if (!this.needsClick(this.targetElement) || this.cancelNextClick) {
                if (C.stopImmediatePropagation) {
                    C.stopImmediatePropagation()
                } else {
                    C.propagationStopped = true
                }
                C.stopPropagation();
                C.preventDefault();
                return false
            }
            return true
        };
        A.prototype.onClick = function (D) {
            var C;
            if (this.trackingClick) {
                this.targetElement = null;
                this.trackingClick = false;
                return true
            }
            if (D.target.type === "submit" && D.detail === 0) {
                return true
            }
            C = this.onMouse(D);
            if (!C) {
                this.targetElement = null
            }
            return C
        };
        A.prototype.destroy = function () {
            var C = this.layer;
            if (this.deviceIsAndroid) {
                C.removeEventListener("mouseover", this.onMouse, true);
                C.removeEventListener("mousedown", this.onMouse, true);
                C.removeEventListener("mouseup", this.onMouse, true)
            }
            C.removeEventListener("click", this.onClick, true);
            C.removeEventListener("touchstart", this.onTouchStart, false);
            C.removeEventListener("touchmove", this.onTouchMove, false);
            C.removeEventListener("touchend", this.onTouchEnd, false);
            C.removeEventListener("touchcancel", this.onTouchCancel, false)
        };
        A.notNeeded = function (D) {
            var C;
            var E;
            if (typeof window.ontouchstart === "undefined") {
                return true
            }
            E = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1];
            if (E) {
                if (A.prototype.deviceIsAndroid) {
                    C = document.querySelector("meta[name=viewport]");
                    if (C) {
                        if (C.content.indexOf("user-scalable=no") !== -1) {
                            return true
                        }
                        if (E > 31 && window.innerWidth <= window.screen.width) {
                            return true
                        }
                    }
                } else {
                    return true
                }
            }
            if (D.style.msTouchAction === "none") {
                return true
            }
            return false
        };
        A.attach = function (C) {
            return new A(C)
        };
        if (typeof define !== "undefined" && define.amd) {
            define(function () {
                return A
            })
        } else {
            if (typeof H !== "undefined" && H.exports) {
                H.exports = A.attach;
                H.exports.FastClick = A
            } else {
                window.FastClick = A
            }
        }
    });
    B.register("switchery/switchery.js", function (I, J, K) {
        var A = J("transitionize"), N = J("fastclick");
        K.exports = M;
        var L = {
            color: "#64bd63",
            secondaryColor: "#dfdfdf",
            className: "switchery",
            disabled: false,
            disabledOpacity: 0.5,
            speed: "0.4s"
        };

        function M(C, E) {
            if (!(this instanceof M)) {
                return new M(C, E)
            }
            this.element = C;
            this.options = E || {};
            for (var D in L) {
                if (this.options[D] == null) {
                    this.options[D] = L[D]
                }
            }
            if (this.element != null && this.element.type == "checkbox") {
                this.init()
            }
        }

        M.prototype.hide = function () {
            this.element.style.display = "none"
        };
        M.prototype.show = function () {
            var C = this.create();
            this.insertAfter(this.element, C)
        };
        M.prototype.create = function () {
            this.switcher = document.createElement("span");
            this.jack = document.createElement("small");
            this.switcher.appendChild(this.jack);
            this.switcher.className = this.options.className;
            return this.switcher
        };
        M.prototype.insertAfter = function (C, D) {
            C.parentNode.insertBefore(D, C.nextSibling)
        };
        M.prototype.isChecked = function () {
            return this.element.checked
        };
        M.prototype.isDisabled = function () {
            return this.options.disabled || this.element.disabled
        };
        M.prototype.setPosition = function (E) {
            var C = this.isChecked(), F = this.switcher, D = this.jack;
            if (E && C) {
                C = false
            } else {
                if (E && !C) {
                    C = true
                }
            }
            if (C === true) {
                this.element.checked = true;
                if (window.getComputedStyle) {
                    D.style.left = parseInt(window.getComputedStyle(F).width) - parseInt(window.getComputedStyle(D).width) + "px"
                } else {
                    D.style.left = parseInt(F.currentStyle["width"]) - parseInt(D.currentStyle["width"]) + "px"
                }
                if (this.options.color) {
                    this.colorize()
                }
                this.setSpeed()
            } else {
                D.style.left = 0;
                this.element.checked = false;
                this.switcher.style.boxShadow = "inset 0 0 0 0 " + this.options.secondaryColor;
                this.switcher.style.borderColor = this.options.secondaryColor;
                this.switcher.style.backgroundColor = "";
                this.setSpeed()
            }
        };
        M.prototype.setSpeed = function () {
            var C = {}, D = {left: this.options.speed.replace(/[a-z]/, "") / 2 + "s"};
            if (this.isChecked()) {
                C = {
                    border: this.options.speed,
                    "box-shadow": this.options.speed,
                    "background-color": this.options.speed.replace(/[a-z]/, "") * 3 + "s"
                }
            } else {
                C = {border: this.options.speed, "box-shadow": this.options.speed}
            }
            A(this.switcher, C);
            A(this.jack, D)
        };
        M.prototype.setAttributes = function () {
            var D = this.element.getAttribute("id"), C = this.element.getAttribute("name");
            if (D) {
                this.switcher.setAttribute("id", D)
            }
            if (C) {
                this.switcher.setAttribute("name", C)
            }
        };
        M.prototype.colorize = function () {
            this.switcher.style.backgroundColor = this.options.color;
            this.switcher.style.borderColor = this.options.color;
            this.switcher.style.boxShadow = "inset 0 0 0 16px " + this.options.color
        };
        M.prototype.handleOnchange = function (D) {
            if (typeof Event === "function" || !document.fireEvent) {
                var C = document.createEvent("HTMLEvents");
                C.initEvent("change", true, true);
                this.element.dispatchEvent(C)
            } else {
                this.element.fireEvent("onchange")
            }
        };
        M.prototype.handleChange = function () {
            var D = this, C = this.element;
            if (C.addEventListener) {
                C.addEventListener("change", function () {
                    D.setPosition()
                })
            } else {
                C.attachEvent("onchange", function () {
                    D.setPosition()
                })
            }
        };
        M.prototype.handleClick = function () {
            var C = this, D = this.switcher;
            if (this.isDisabled() === false) {
                N(D);
                if (D.addEventListener) {
                    D.addEventListener("click", function () {
                        C.setPosition(true);
                        C.handleOnchange(C.element.checked)
                    })
                } else {
                    D.attachEvent("onclick", function () {
                        C.setPosition(true);
                        C.handleOnchange(C.element.checked)
                    })
                }
            } else {
                this.element.disabled = true;
                this.switcher.style.opacity = this.options.disabledOpacity
            }
        };
        M.prototype.disableLabel = function () {
            var C = this.element.parentNode, E = document.getElementsByTagName("label"), F = null;
            for (var D = 0; D < E.length; D++) {
                if (E[D].getAttribute("for") === this.element.id) {
                    F = true
                }
            }
            if (F === true || C.tagName.toLowerCase() === "label") {
                if (C.addEventListener) {
                    C.addEventListener("click", function (G) {
                        G.preventDefault()
                    })
                } else {
                    C.attachEvent("onclick", function (G) {
                        G.returnValue = false
                    })
                }
            }
        };
        M.prototype.markAsSwitched = function () {
            this.element.setAttribute("data-switchery", true)
        };
        M.prototype.markedAsSwitched = function () {
            return this.element.getAttribute("data-switchery")
        };
        M.prototype.init = function () {
            this.hide();
            this.show();
            this.setPosition();
            this.setAttributes();
            this.markAsSwitched();
            this.disableLabel();
            this.handleChange();
            this.handleClick()
        }
    });
    B.alias("abpetkov-transitionize/transitionize.js", "switchery/deps/transitionize/transitionize.js");
    B.alias("abpetkov-transitionize/transitionize.js", "switchery/deps/transitionize/index.js");
    B.alias("abpetkov-transitionize/transitionize.js", "transitionize/index.js");
    B.alias("abpetkov-transitionize/transitionize.js", "abpetkov-transitionize/index.js");
    B.alias("ftlabs-fastclick/lib/fastclick.js", "switchery/deps/fastclick/lib/fastclick.js");
    B.alias("ftlabs-fastclick/lib/fastclick.js", "switchery/deps/fastclick/index.js");
    B.alias("ftlabs-fastclick/lib/fastclick.js", "fastclick/index.js");
    B.alias("ftlabs-fastclick/lib/fastclick.js", "ftlabs-fastclick/index.js");
    B.alias("switchery/switchery.js", "switchery/index.js");
    if (typeof exports == "object") {
        module.exports = B("switchery")
    } else {
        if (typeof define == "function" && define.amd) {
            define(function () {
                return B("switchery")
            })
        } else {
            this["Switchery"] = B("switchery")
        }
    }
})();