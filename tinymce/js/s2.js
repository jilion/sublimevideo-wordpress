/*!
 *  script.aculo.us version 2.0.0_b1
 *  (c) 2005-2010 Thomas Fuchs
 *
 *  script.aculo.us is freely distributable under the terms of an MIT-style license.
 *----------------------------------------------------------------------------------*/



var S2 = {
  Version: '2.0.0_b1',

  Extensions: {}
};


Function.prototype.optionize = function(){
  var self = this, argumentNames = self.argumentNames(), optionIndex = this.length - 1;

  var method = function() {
    var args = $A(arguments);

    var options = (typeof args.last() === 'object') ? args.pop() : {};
    var prefilledArgs = [];
    if (optionIndex > 0) {
      prefilledArgs = ((args.length > 0 ? args : [null]).inGroupsOf(
       optionIndex).flatten()).concat(options);
    }

    return self.apply(this, prefilledArgs);
  };
  method.argumentNames = function() { return argumentNames; };
  return method;
};

Function.ABSTRACT = function() {
  throw "Abstract method. Implement in subclass.";
};

Object.extend(Number.prototype, {
  constrain: function(n1, n2) {
    var min = (n1 < n2) ? n1 : n2;
    var max = (n1 < n2) ? n2 : n1;

    var num = Number(this);

    if (num < min) num = min;
    if (num > max) num = max;

    return num;
  },

  nearer: function(n1, n2) {
    var num = Number(this);

    var diff1 = Math.abs(num - n1);
    var diff2 = Math.abs(num - n2);

    return (diff1 < diff2) ? n1 : n2;
  },

  tween: function(target, position) {
    return this + (target-this) * position;
  }
});


Object.propertize = function(property, object){
  return Object.isString(property) ? object[property] : property;
};

S2.CSS = {
  PROPERTY_MAP: {
    backgroundColor: 'color',
    borderBottomColor: 'color',
    borderBottomWidth: 'length',
    borderLeftColor: 'color',
    borderLeftWidth: 'length',
    borderRightColor: 'color',
    borderRightWidth: 'length',
    borderSpacing: 'length',
    borderTopColor: 'color',
    borderTopWidth: 'length',
    bottom: 'length',
    color: 'color',
    fontSize: 'length',
    fontWeight: 'integer',
    height: 'length',
    left: 'length',
    letterSpacing: 'length',
    lineHeight: 'length',
    marginBottom: 'length',
    marginLeft: 'length',
    marginRight: 'length',
    marginTop: 'length',
    maxHeight: 'length',
    maxWidth: 'length',
    minHeight: 'length',
    minWidth: 'length',
    opacity: 'number',
    outlineColor: 'color',
    outlineOffset: 'length',
    outlineWidth: 'length',
    paddingBottom: 'length',
    paddingLeft: 'length',
    paddingRight: 'length',
    paddingTop: 'length',
    right: 'length',
    textIndent: 'length',
    top: 'length',
    width: 'length',
    wordSpacing: 'length',
    zIndex: 'integer',
    zoom: 'number'
  },

  VENDOR: {
    PREFIX: null,

    LOOKUP_PREFIXES: ['webkit', 'Moz', 'O'],
    LOOKUP_PROPERTIES: $w('BorderRadius BoxShadow Transform Transition ' +
     'TransitionDuration TransitionTimingFunction TransitionProperty ' +
     'TransitionDelay ' +
     'BorderTopLeftRadius BorderTopRightRadius BorderBottomLeftRadius ' +
     'BorderBottomRightRadius'
    ),
    LOOKUP_EDGE_CASES: {
      'BorderTopLeftRadius': 'BorderRadiusTopleft',
      'BorderTopRightRadius': 'BorderRadiusTopright',
      'BorderBottomLeftRadius': 'BorderRadiusBottomleft',
      'BorderBottomRightRadius': 'BorderRadiusBottomright'
    },

    PROPERTY_MAP: {}
  },

  LENGTH: /^(([\+\-]?[0-9\.]+)(em|ex|px|in|cm|mm|pt|pc|\%))|0$/,

  NUMBER: /([\+-]*\d+\.?\d*)/,

  __parseStyleElement: document.createElement('div'),

  parseStyle: function(styleString) {
    S2.CSS.__parseStyleElement.innerHTML = '<div style="' + styleString + '"></div>';
    var style = S2.CSS.__parseStyleElement.childNodes[0].style, styleRules = {};

    S2.CSS.NUMERIC_PROPERTIES.each( function(property){
      if (style[property]) styleRules[property] = style[property];
    });

    S2.CSS.COLOR_PROPERTIES.each( function(property){
      if (style[property]) styleRules[property] = S2.CSS.colorFromString(style[property]);
    });

    if (Prototype.Browser.IE && styleString.include('opacity'))
      styleRules.opacity = styleString.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1];

    return styleRules;
  },

  parse: function(styleString) {
    return S2.CSS.parseStyle(styleString);
  },

  normalize: function(element, style) {
    if (Object.isHash(style)) style = style.toObject();
    if (typeof style === 'string') style = S2.CSS.parseStyle(style);

    var result = {}, current;

    for (var property in style) {
      current = element.getStyle(property);
      if (style[property] !== current) {
        result[property] = style[property];
      }
    }

    return result;
  },

  serialize: function(object) {
    if (Object.isHash(object)) object = object.toObject();
    var output = "", value;
    for (var property in object) {
      value = object[property];
      property = this.vendorizeProperty(property);
      output += (property + ": " + value + ';');
    }
    return output;
  },

  vendorizeProperty: function(property) {
    property = property.underscore().dasherize();

    if (property in S2.CSS.VENDOR.PROPERTY_MAP) {
      property = S2.CSS.VENDOR.PROPERTY_MAP[property];
    }

    return property;
  },

  normalizeColor: function(color) {
    if (!color || color == 'rgba(0, 0, 0, 0)' || color == 'transparent') color = '#ffffff';
    color = S2.CSS.colorFromString(color);
    return [
      parseInt(color.slice(1,3),16), parseInt(color.slice(3,5),16), parseInt(color.slice(5,7),16)
    ];
  },

  colorFromString: function(color) {
    var value = '#', cols, i;
    if (color.slice(0,4) == 'rgb(') {
      cols = color.slice(4,color.length-1).split(',');
      i=3; while(i--) value += parseInt(cols[2-i]).toColorPart();
    } else if (color.slice(0,1) == '#') {
        if (color.length==4) for(i=1;i<4;i++) value += (color.charAt(i) + color.charAt(i)).toLowerCase();
        if (color.length==7) value = color.toLowerCase();
    } else { value = color; }
    return (value.length==7 ? value : (arguments[1] || value));
  },

  interpolateColor: function(from, to, position){
    from = S2.CSS.normalizeColor(from);
    to = S2.CSS.normalizeColor(to);

    return '#' + [0,1,2].map(function(index){
      return Math.max(Math.min(from[index].tween(to[index], position).round(), 255), 0).toColorPart();
    }).join('');
  },

  interpolateNumber: function(from, to, position){
    return 1*((from||0).tween(to, position).toFixed(3));
  },

  interpolateLength: function(from, to, position){
    if (!from || parseFloat(from) === 0) {
      from = '0' + to.gsub(S2.CSS.NUMBER,'');
    }
    to.scan(S2.CSS.NUMBER, function(match){ to = 1*(match[1]); });
    return from.gsub(S2.CSS.NUMBER, function(match){
      return (1*(parseFloat(match[1]).tween(to, position).toFixed(3))).toString();
    });
  },

  interpolateInteger: function(from, to, position){
    return parseInt(from).tween(to, position).round();
  },

  interpolate: function(property, from, to, position){
    return S2.CSS['interpolate'+S2.CSS.PROPERTY_MAP[property.camelize()].capitalize()](from, to, position);
  },

  ElementMethods: {
    getStyles: function(element) {
      var css = document.defaultView.getComputedStyle($(element), null);
      return S2.CSS.PROPERTIES.inject({ }, function(styles, property) {
        styles[property] = css[property];
        return styles;
      });
    }
  }
};

S2.CSS.PROPERTIES = [];
for (var property in S2.CSS.PROPERTY_MAP) S2.CSS.PROPERTIES.push(property);

S2.CSS.NUMERIC_PROPERTIES = S2.CSS.PROPERTIES.findAll(function(property){ return !property.endsWith('olor') });
S2.CSS.COLOR_PROPERTIES   = S2.CSS.PROPERTIES.findAll(function(property){ return property.endsWith('olor') });

if (!(document.defaultView && document.defaultView.getComputedStyle)) {
  S2.CSS.ElementMethods.getStyles = function(element) {
    element = $(element);
    var css = element.currentStyle, styles;
    styles = S2.CSS.PROPERTIES.inject({ }, function(hash, property) {
      hash[property] = css[property];
      return hash;
    });
    if (!styles.opacity) styles.opacity = element.getOpacity();
    return styles;
  };
};

Element.addMethods(S2.CSS.ElementMethods);

(function() {
  var div = document.createElement('div');
  var style = div.style, prefix = null;
  var edgeCases = S2.CSS.VENDOR.LOOKUP_EDGE_CASES;
  var uncamelize = function(prop, prefix) {
    if (prefix) {
      prop = '-' + prefix.toLowerCase() + '-' + uncamelize(prop);
    }
    return prop.underscore().dasherize();
  }

  S2.CSS.VENDOR.LOOKUP_PROPERTIES.each(function(prop) {
    if (!prefix) { // We attempt to detect a prefix
      prefix = S2.CSS.VENDOR.LOOKUP_PREFIXES.detect( function(p) {
        return !Object.isUndefined(style[p + prop]);
      });
    }
    if (prefix) { // If we detected a prefix
      if ((prefix + prop) in style) {
        S2.CSS.VENDOR.PROPERTY_MAP[uncamelize(prop)] = uncamelize(prop, prefix);
      } else if (prop in edgeCases && (prefix + edgeCases[prop]) in style) {
        S2.CSS.VENDOR.PROPERTY_MAP[uncamelize(prop)] = uncamelize(edgeCases[prop], prefix);
      }
    }
  });

  S2.CSS.VENDOR.PREFIX = prefix;

  div = null;
})();

S2.FX = (function(){
  var queues = [], globalQueue,
    heartbeat, activeEffects = 0;

  function beatOnDemand(dir) {
    activeEffects += dir;
    if (activeEffects > 0) heartbeat.start();
    else heartbeat.stop();
  }

  function renderQueues() {
    var timestamp = heartbeat.getTimestamp();
    for (var i = 0, queue; queue = queues[i]; i++) {
      queue.render(timestamp);
    }
  }

  function initialize(initialHeartbeat){
    if (globalQueue) return;
    queues.push(globalQueue = new S2.FX.Queue());
    S2.FX.DefaultOptions.queue = globalQueue;
    heartbeat = initialHeartbeat || new S2.FX.Heartbeat();

    document
      .observe('effect:heartbeat', renderQueues)
      .observe('effect:queued',    beatOnDemand.curry(1))
      .observe('effect:dequeued',  beatOnDemand.curry(-1));
  }

  function formatTimestamp(timestamp) {
    if (!timestamp) timestamp = (new Date()).valueOf();
    var d = new Date(timestamp);
    return d.getSeconds() + '.' + d.getMilliseconds() + 's';
  }

  return {
    initialize:   initialize,
    getQueues:    function() { return queues; },
    addQueue:     function(queue) { queues.push(queue); },
    getHeartbeat: function() { return heartbeat; },
    setHeartbeat: function(newHeartbeat) { heartbeat = newHeartbeat; },
    formatTimestamp: formatTimestamp
  }
})();

Object.extend(S2.FX, {
  DefaultOptions: {
    transition: 'sinusoidal',
    position:   'parallel',
    fps:        60,
    duration:   .2
  },

  elementDoesNotExistError: {
    name: 'ElementDoesNotExistError',
    message: 'The specified DOM element does not exist, but is required for this effect to operate'
  },

  parseOptions: function(options) {
    if (Object.isNumber(options))
      options = { duration: options };
    else if (Object.isFunction(options))
      options = { after: options };
    else if (Object.isString(options))
      options = { duration: options == 'slow' ? 1 : options == 'fast' ? .1 : .2 };

    return options || {};
  },

  ready: function(element) {
    if (!element) return;
    var table = this._ready;
    var uid = element._prototypeUID;
    if (!uid) return true;

    bool = table[uid];
    return Object.isUndefined(bool) ? true : bool;
  },

  setReady: function(element, bool) {
    if (!element) return;
    var table = this._ready, uid = element._prototypeUID;
    if (!uid) {
      element.getStorage();
      uid = element._prototypeUID;
    }

    table[uid] = bool;
  },

  _ready: {}
});

S2.FX.Base = Class.create({
  initialize: function(options) {
    S2.FX.initialize();
    this.updateWithoutWrappers = this.update;

    if(options && options.queue && !S2.FX.getQueues().include(options.queue))
      S2.FX.addQueue(options.queue);

    this.setOptions(options);
    this.duration = this.options.duration*1000;
    this.state = 'idle';

    ['after','before'].each(function(method) {
      this[method] = function(method) {
        method(this);
        return this;
      }
    }, this);
  },

  setOptions: function(options) {
    options = S2.FX.parseOptions(options);

    this.options = Object.extend(this.options || Object.extend({}, S2.FX.DefaultOptions), options);

    if (options.tween) this.options.transition = options.tween;

    if (this.options.beforeUpdate || this.options.afterUpdate) {
      this.update = this.updateWithoutWrappers.wrap( function(proceed,position){
        if (this.options.beforeUpdate) this.options.beforeUpdate(this, position);
        proceed(position);
        if (this.options.afterUpdate) this.options.afterUpdate(this, position);
      }.bind(this));
    }

    if (this.options.transition === false)
      this.options.transition = S2.FX.Transitions.linear;

    this.options.transition = Object.propertize(this.options.transition, S2.FX.Transitions);
  },

  play: function(options) {
    this.setOptions(options);
    this.frameCount = 0;
    this.state = 'idle';
    this.options.queue.add(this);
    this.maxFrames = this.options.fps * this.duration / 1000;
    return this;
  },

  render: function(timestamp) {
    if (this.options.debug) {
    }
    if (timestamp >= this.startsAt) {
      if (this.state == 'idle' && (!this.element || S2.FX.ready(this.element))) {
        this.debug('starting the effect at ' + S2.FX.formatTimestamp(timestamp));
        this.endsAt = this.startsAt + this.duration;
        this.start();
        this.frameCount++;
        return this;
      }

      if (timestamp >= this.endsAt && this.state !== 'finished') {
        this.debug('stopping the effect at ' + S2.FX.formatTimestamp(timestamp));
        this.finish();
        return this;
      }

      if (this.state === 'running') {
        var position = 1 - (this.endsAt - timestamp) / this.duration;
        if ((this.maxFrames * position).floor() > this.frameCount) {
          this.update(this.options.transition(position));
          this.frameCount++;
        }
      }
    }
    return this;
  },

  start: function() {
    if (this.options.before) this.options.before(this);
    if (this.setup) this.setup();
    this.state = 'running';
    this.update(this.options.transition(0));
  },

  cancel: function(after) {
    if (this.state !== 'running') return;
    if (this.teardown) this.teardown();
    if (after && this.options.after) this.options.after(this);
    this.state = 'finished';
  },

  finish: function() {
    if (this.state !== 'running') return;
    this.update(this.options.transition(1));
    this.cancel(true);
  },

  inspect: function() {
    return '#<S2.FX:' + [this.state, this.startsAt, this.endsAt].inspect() + '>';
  },

  update: Prototype.emptyFunction,

  debug: function(message) {
    if (!this.options.debug) return;
    if (window.console && console.log) {
      console.log(message);
    }
  }
});

S2.FX.Element = Class.create(S2.FX.Base, {
  initialize: function($super, element, options) {
    if(!(this.element = $(element)))
      throw(S2.FX.elementDoesNotExistError);
    this.operators = [];
    return $super(options);
  },

  animate: function() {
    var args = $A(arguments), operator =  args.shift();
    operator = operator.charAt(0).toUpperCase() + operator.substring(1);
    this.operators.push(new S2.FX.Operators[operator](this, args[0], args[1] || {}));
  },

  play: function($super, element, options) {
    if (element) this.element = $(element);
    this.operators = [];
    return $super(options);
  },

  update: function(position) {
    for (var i = 0, operator; operator = this.operators[i]; i++) {
      operator.render(position);
    }
  }
});
S2.FX.Heartbeat = Class.create({
  initialize: function(options) {
    this.options = Object.extend({
      framerate: Prototype.Browser.MobileSafari ? 20 : 60
    }, options);
    this.beat = this.beat.bind(this);
  },

  start: function() {
    if (this.heartbeatInterval) return;
    this.heartbeatInterval =
      setInterval(this.beat, 1000/this.options.framerate);
    this.updateTimestamp();
  },

  stop: function() {
    if (!this.heartbeatInterval) return;
    clearInterval(this.heartbeatInterval);
    this.heartbeatInterval = null;
    this.timestamp = null;
  },

  beat: function() {
    this.updateTimestamp();
    document.fire('effect:heartbeat');
  },

  getTimestamp: function() {
    return this.timestamp || this.generateTimestamp();
  },

  generateTimestamp: function() {
    return new Date().getTime();
  },

  updateTimestamp: function() {
    this.timestamp = this.generateTimestamp();
  }
});
S2.FX.Queue = (function(){
  return function() {
    var instance = this;
    var effects = [];

    function getEffects() {
      return effects;
    }

    function active() {
      return effects.length > 0;
    }

    function add(effect) {
      calculateTiming(effect);
      effects.push(effect);
      document.fire('effect:queued', instance);
      return instance;
    }

    function remove(effect) {
      effects = effects.without(effect);
      document.fire('effect:dequeued', instance);
      delete effect;
      return instance;
    }

    function render(timestamp) {
      for (var i = 0, effect; effect = effects[i]; i++) {
        effect.render(timestamp);
        if (effect.state === 'finished') remove(effect);
      }

      return instance;
    }

    function calculateTiming(effect) {
      var position = effect.options.position || 'parallel',
        now = S2.FX.getHeartbeat().getTimestamp();

      var startsAt;
      if (position === 'end') {
        startsAt = effects.without(effect).pluck('endsAt').max() || now;
        if (startsAt < now) startsAt = now;
      } else {
        startsAt = now;
      }

      var delay = (effect.options.delay || 0) * 1000;
      effect.startsAt = startsAt + delay;

      var duration = (effect.options.duration || 1) * 1000;
      effect.endsAt = effect.startsAt + duration;
    }

    Object.extend(instance, {
      getEffects: getEffects,
      active: active,
      add: add,
      remove: remove,
      render: render
    });
  }
})();

S2.FX.Attribute = Class.create(S2.FX.Base, {
  initialize: function($super, object, from, to, options, method) {
    object = Object.isString(object) ? $(object) : object;

    this.method = Object.isFunction(method) ? method.bind(object) :
      Object.isFunction(object[method]) ? object[method].bind(object) :
      function(value) { object[method] = value };

    this.to = to;
    this.from = from;

    return $super(options);
  },

  update: function(position) {
    this.method(this.from.tween(this.to, position));
  }
});
S2.FX.Style = Class.create(S2.FX.Element, {
  setup: function() {
    this.animate('style', this.element, { style: this.options.style });
  }
});
S2.FX.Operators = { };

S2.FX.Operators.Base = Class.create({
  initialize: function(effect, object, options) {
    this.effect = effect;
    this.object = object;
    this.options = Object.extend({
      transition: Prototype.K
    }, options);
  },

  inspect: function() {
    return "#<S2.FX.Operators.Base:" + this.lastValue + ">";
  },

  setup: function() {
  },

  valueAt: function(position) {
  },

  applyValue: function(value) {
  },

  render: function(position) {
    var value = this.valueAt(this.options.transition(position));
    this.applyValue(value);
    this.lastValue = value;
  }
});

S2.FX.Operators.Style = Class.create(S2.FX.Operators.Base, {
  initialize: function($super, effect, object, options) {
    $super(effect, object, options);
    this.element = $(this.object);

    this.style = Object.isString(this.options.style) ?
      S2.CSS.parseStyle(this.options.style) : this.options.style;

    var translations = this.options.propertyTransitions || {};

    this.tweens = [];

    for (var item in this.style) {
      var property = item.underscore().dasherize(),
       from = this.element.getStyle(property), to = this.style[item];

      if (from != to) {
        this.tweens.push([
          property,
          S2.CSS.interpolate.curry(property, from, to),
          item in translations ?
           Object.propertize(translations[item], S2.FX.Transitions) :
           Prototype.K
        ]);
      }
    }
  },

  valueAt: function(position) {
    return this.tweens.map( function(tween){
      return tween[0] + ':' + tween[1](tween[2](position));
    }).join(';');
  },

  applyValue: function(value) {
    if (this.currentStyle == value) return;
    this.element.setStyle(value);
    this.currentStyle = value;
  }
});

S2.FX.Morph = Class.create(S2.FX.Element, {
  setup: function() {
    if (this.options.change)
      this.setupWrappers();
    else if (this.options.style)
      this.animate('style', this.destinationElement || this.element, {
        style: this.options.style,
        propertyTransitions: this.options.propertyTransitions || { }
      });
  },

  teardown: function() {
    if (this.options.change)
      this.teardownWrappers();
  },

  setupWrappers: function() {
    var elementFloat = this.element.getStyle("float"),
      sourceHeight, sourceWidth,
      destinationHeight, destinationWidth,
      maxHeight;

    this.transitionElement = new Element('div').setStyle({ position: "relative", overflow: "hidden", 'float': elementFloat });
    this.element.setStyle({ 'float': "none" }).insert({ before: this.transitionElement });

    this.sourceElementWrapper = this.element.cloneWithoutIDs().wrap('div');
    this.destinationElementWrapper = this.element.wrap('div');

    this.transitionElement.insert(this.sourceElementWrapper).insert(this.destinationElementWrapper);

    sourceHeight = this.sourceElementWrapper.getHeight();
    sourceWidth = this.sourceElementWrapper.getWidth();

    this.options.change();

    destinationHeight = this.destinationElementWrapper.getHeight();
    destinationWidth  = this.destinationElementWrapper.getWidth();

    this.outerWrapper = new Element("div");
    this.transitionElement.insert({ before: this.outerWrapper });
    this.outerWrapper.setStyle({
      overflow: "hidden", height: sourceHeight + "px", width: sourceWidth + "px"
    }).appendChild(this.transitionElement);

    maxHeight = Math.max(destinationHeight, sourceHeight), maxWidth = Math.max(destinationWidth, sourceWidth);

    this.transitionElement.setStyle({ height: sourceHeight + "px", width: sourceWidth + "px" });
    this.sourceElementWrapper.setStyle({ position: "absolute", height: maxHeight + "px", width: maxWidth + "px", top: 0, left: 0 });
    this.destinationElementWrapper.setStyle({ position: "absolute", height: maxHeight + "px", width: maxWidth + "px", top: 0, left: 0, opacity: 0, zIndex: 2000 });

    this.outerWrapper.insert({ before: this.transitionElement }).remove();

    this.animate('style', this.transitionElement, { style: 'height:' + destinationHeight + 'px; width:' + destinationWidth + 'px' });
    this.animate('style', this.destinationElementWrapper, { style: 'opacity: 1.0' });
  },

  teardownWrappers: function() {
    var destinationElement = this.destinationElementWrapper.down();

    if (destinationElement)
      this.transitionElement.insert({ before: destinationElement });

    this.transitionElement.remove();
  }
});
S2.FX.Parallel = Class.create(S2.FX.Base, {
  initialize: function($super, effects, options) {
    this.effects = effects || [];
    return $super(options || {});
  },

  setup: function() {
    this.effects.invoke('setup');
  },

  update: function(position) {
    this.effects.invoke('update', position);
  },

  cancel: function($super, after) {
    $super(after);
    this.effects.invoke('cancel', after);
  },

  start: function($super) {
    $super();
    this.effects.invoke('start');
  }
});

S2.FX.Operators.Scroll = Class.create(S2.FX.Operators.Base, {
  initialize: function($super, effect, object, options) {
    $super(effect, object, options);
    this.start = object.scrollTop;
    this.end = this.options.scrollTo;
  },

  valueAt: function(position) {
    return this.start + ((this.end - this.start)*position);
  },

  applyValue: function(value){
    this.object.scrollTop = value.round();
  }
});

S2.FX.Scroll = Class.create(S2.FX.Element, {
  setup: function() {
    this.animate('scroll', this.element, { scrollTo: this.options.to });
  }
});

S2.FX.SlideDown = Class.create(S2.FX.Element, {
  setup: function() {
    var element = this.destinationElement || this.element;
    var layout = element.getLayout();

    var style = {
      height:        layout.get('height') + 'px',
      paddingTop:    layout.get('padding-top') + 'px',
      paddingBottom: layout.get('padding-bottom') + 'px'
    };

    element.setStyle({
      height:         '0',
      paddingTop:     '0',
      paddingBottom:  '0',
      overflow:       'hidden'
    }).show();

    this.animate('style', element, {
      style: style,
      propertyTransitions: {}
    });
  },

  teardown: function() {
    var element = this.destinationElement || this.element;
    element.setStyle({
      height:         '',
      paddingTop:     '',
      paddingBottom:  '',
      overflow:       'visible'
    });
  }
});

S2.FX.SlideUp = Class.create(S2.FX.Morph, {
  setup: function() {
    var element = this.destinationElement || this.element;
    var layout = element.getLayout();

    var style = {
      height:        '0px',
      paddingTop:    '0px',
      paddingBottom: '0px'
    };

    element.setStyle({ overflow: 'hidden' });

    this.animate('style', element, {
      style: style,
      propertyTransitions: {}
    });
  },

  teardown: function() {
    var element = this.destinationElement || this.element;
    element.setStyle({
      height:         '',
      paddingTop:     '',
      paddingBottom:  '',
      overflow:       'visible'
    }).hide();
  }
});


S2.FX.Transitions = {

  linear: Prototype.K,

  sinusoidal: function(pos) {
    return (-Math.cos(pos*Math.PI)/2) + 0.5;
  },

  reverse: function(pos) {
    return 1 - pos;
  },

  mirror: function(pos, transition) {
    transition = transition || S2.FX.Transitions.sinusoidal;
    if(pos<0.5)
      return transition(pos*2);
    else
      return transition(1-(pos-0.5)*2);
  },

  flicker: function(pos) {
    var pos = pos + (Math.random()-0.5)/5;
    return S2.FX.Transitions.sinusoidal(pos < 0 ? 0 : pos > 1 ? 1 : pos);
  },

  wobble: function(pos) {
    return (-Math.cos(pos*Math.PI*(9*pos))/2) + 0.5;
  },

  pulse: function(pos, pulses) {
    return (-Math.cos((pos*((pulses||5)-.5)*2)*Math.PI)/2) + .5;
  },

  blink: function(pos, blinks) {
    return Math.round(pos*(blinks||5)) % 2;
  },

  spring: function(pos) {
    return 1 - (Math.cos(pos * 4.5 * Math.PI) * Math.exp(-pos * 6));
  },

  none: Prototype.K.curry(0),

  full: Prototype.K.curry(1)
};

/*!
 *  Copyright (c) 2006 Apple Computer, Inc. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *
 *  1. Redistributions of source code must retain the above copyright notice,
 *  this list of conditions and the following disclaimer.
 *
 *  2. Redistributions in binary form must reproduce the above copyright notice,
 *  this list of conditions and the following disclaimer in the documentation
 *  and/or other materials provided with the distribution.
 *
 *  3. Neither the name of the copyright holder(s) nor the names of any
 *  contributors may be used to endorse or promote products derived from
 *  this software without specific prior written permission.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 *  THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 *  ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 *  FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

(function(){
  function CubicBezierAtTime(t,p1x,p1y,p2x,p2y,duration) {
    var ax=0,bx=0,cx=0,ay=0,by=0,cy=0;
    function sampleCurveX(t) {return ((ax*t+bx)*t+cx)*t;};
    function sampleCurveY(t) {return ((ay*t+by)*t+cy)*t;};
    function sampleCurveDerivativeX(t) {return (3.0*ax*t+2.0*bx)*t+cx;};
    function solveEpsilon(duration) {return 1.0/(200.0*duration);};
    function solve(x,epsilon) {return sampleCurveY(solveCurveX(x,epsilon));};
    function fabs(n) {if(n>=0) {return n;}else {return 0-n;}};
    function solveCurveX(x,epsilon) {
      var t0,t1,t2,x2,d2,i;
      for(t2=x, i=0; i<8; i++) {x2=sampleCurveX(t2)-x; if(fabs(x2)<epsilon) {return t2;} d2=sampleCurveDerivativeX(t2); if(fabs(d2)<1e-6) {break;} t2=t2-x2/d2;}
      t0=0.0; t1=1.0; t2=x; if(t2<t0) {return t0;} if(t2>t1) {return t1;}
      while(t0<t1) {x2=sampleCurveX(t2); if(fabs(x2-x)<epsilon) {return t2;} if(x>x2) {t0=t2;}else {t1=t2;} t2=(t1-t0)*.5+t0;}
      return t2; // Failure.
    };
    cx=3.0*p1x; bx=3.0*(p2x-p1x)-cx; ax=1.0-cx-bx; cy=3.0*p1y; by=3.0*(p2y-p1y)-cy; ay=1.0-cy-by;
    return solve(t, solveEpsilon(duration));
  }
  S2.FX.cubicBezierTransition = function(x1, y1, x2, y2){
    return (function(pos){
      return CubicBezierAtTime(pos,x1,y1,x2,y2,1);
    });
  }
})();

S2.FX.Transitions.webkitCubic =
  S2.FX.cubicBezierTransition(0.25,0.1,0.25,1);
S2.FX.Transitions.webkitEaseInOut =
  S2.FX.cubicBezierTransition(0.42,0.0,0.58,1.0);

/*!
 *  TERMS OF USE - EASING EQUATIONS
 *  Open source under the BSD License.
 *  Easing Equations (c) 2003 Robert Penner, all rights reserved.
 */

Object.extend(S2.FX.Transitions, {
  easeInQuad: function(pos){
     return Math.pow(pos, 2);
  },

  easeOutQuad: function(pos){
    return -(Math.pow((pos-1), 2) -1);
  },

  easeInOutQuad: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,2);
    return -0.5 * ((pos-=2)*pos - 2);
  },

  easeInCubic: function(pos){
    return Math.pow(pos, 3);
  },

  easeOutCubic: function(pos){
    return (Math.pow((pos-1), 3) +1);
  },

  easeInOutCubic: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,3);
    return 0.5 * (Math.pow((pos-2),3) + 2);
  },

  easeInQuart: function(pos){
    return Math.pow(pos, 4);
  },

  easeOutQuart: function(pos){
    return -(Math.pow((pos-1), 4) -1)
  },

  easeInOutQuart: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,4);
    return -0.5 * ((pos-=2)*Math.pow(pos,3) - 2);
  },

  easeInQuint: function(pos){
    return Math.pow(pos, 5);
  },

  easeOutQuint: function(pos){
    return (Math.pow((pos-1), 5) +1);
  },

  easeInOutQuint: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,5);
    return 0.5 * (Math.pow((pos-2),5) + 2);
  },

  easeInSine: function(pos){
    return -Math.cos(pos * (Math.PI/2)) + 1;
  },

  easeOutSine: function(pos){
    return Math.sin(pos * (Math.PI/2));
  },

  easeInOutSine: function(pos){
    return (-.5 * (Math.cos(Math.PI*pos) -1));
  },

  easeInExpo: function(pos){
    return (pos==0) ? 0 : Math.pow(2, 10 * (pos - 1));
  },

  easeOutExpo: function(pos){
    return (pos==1) ? 1 : -Math.pow(2, -10 * pos) + 1;
  },

  easeInOutExpo: function(pos){
    if(pos==0) return 0;
    if(pos==1) return 1;
    if((pos/=0.5) < 1) return 0.5 * Math.pow(2,10 * (pos-1));
    return 0.5 * (-Math.pow(2, -10 * --pos) + 2);
  },

  easeInCirc: function(pos){
    return -(Math.sqrt(1 - (pos*pos)) - 1);
  },

  easeOutCirc: function(pos){
    return Math.sqrt(1 - Math.pow((pos-1), 2))
  },

  easeInOutCirc: function(pos){
    if((pos/=0.5) < 1) return -0.5 * (Math.sqrt(1 - pos*pos) - 1);
    return 0.5 * (Math.sqrt(1 - (pos-=2)*pos) + 1);
  },

  easeOutBounce: function(pos){
    if ((pos) < (1/2.75)) {
      return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
      return (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
      return (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
      return (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  easeInBack: function(pos){
    var s = 1.70158;
    return (pos)*pos*((s+1)*pos - s);
  },

  easeOutBack: function(pos){
    var s = 1.70158;
    return (pos=pos-1)*pos*((s+1)*pos + s) + 1;
  },

  easeInOutBack: function(pos){
    var s = 1.70158;
    if((pos/=0.5) < 1) return 0.5*(pos*pos*(((s*=(1.525))+1)*pos -s));
    return 0.5*((pos-=2)*pos*(((s*=(1.525))+1)*pos +s) +2);
  },

  elastic: function(pos) {
    return -1 * Math.pow(4,-8*pos) * Math.sin((pos*6-1)*(2*Math.PI)/2) + 1;
  },

  swingFromTo: function(pos) {
    var s = 1.70158;
    return ((pos/=0.5) < 1) ? 0.5*(pos*pos*(((s*=(1.525))+1)*pos - s)) :
      0.5*((pos-=2)*pos*(((s*=(1.525))+1)*pos + s) + 2);
  },

  swingFrom: function(pos) {
    var s = 1.70158;
    return pos*pos*((s+1)*pos - s);
  },

  swingTo: function(pos) {
    var s = 1.70158;
    return (pos-=1)*pos*((s+1)*pos + s) + 1;
  },

  bounce: function(pos) {
    if (pos < (1/2.75)) {
        return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
        return (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
        return (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
        return (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  bouncePast: function(pos) {
    if (pos < (1/2.75)) {
        return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
        return 2 - (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
        return 2 - (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
        return 2 - (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  easeFromTo: function(pos) {
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,4);
    return -0.5 * ((pos-=2)*Math.pow(pos,3) - 2);
  },

  easeFrom: function(pos) {
    return Math.pow(pos,4);
  },

  easeTo: function(pos) {
    return Math.pow(pos,0.25);
  }
});
(function(FX) {

  Hash.addMethods({
    hasKey: function(key) {
      return (key in this._object);
    }
  });

  var supported = false;
  var hardwareAccelerationSupported = false;
  var transitionEndEventName = null;

  function isHWAcceleratedSafari() {
    var ua = navigator.userAgent, av = navigator.appVersion;
    return (!ua.include('Chrome') && av.include('10_6')) ||
     Prototype.Browser.MobileSafari;
  }

  (function() {
    var eventNames = {
      'WebKitTransitionEvent': 'webkitTransitionEnd',
      'TransitionEvent': 'transitionend',
      'OTransitionEvent': 'oTransitionEnd'
    };
    if (S2.CSS.VENDOR.PREFIX) {
      var p = S2.CSS.VENDOR.PREFIX;
      eventNames[p + 'TransitionEvent'] = p + 'TransitionEnd';
    }

    for (var e in eventNames) {
      try {
        document.createEvent(e);
        transitionEndEventName = eventNames[e];
        supported = true;
        if (e == 'WebKitTransitionEvent') {
          hardwareAccelerationSupported = isHWAcceleratedSafari();
        }
        return;
      } catch (ex) { }
    }
  })();

  if (!supported) return;


  Prototype.BrowserFeatures.CSSTransitions = true;
  S2.Extensions.CSSTransitions = true;
  S2.Extensions.HardwareAcceleratedCSSTransitions = hardwareAccelerationSupported;

  if (hardwareAccelerationSupported) {
    Object.extend(FX.DefaultOptions, { accelerate: true });
  }

  function v(prop) {
    return S2.CSS.vendorizeProperty(prop);
  }

  var CSS_TRANSITIONS_PROPERTIES = $w(
   'border-top-left-radius border-top-right-radius ' +
   'border-bottom-left-radius border-bottom-right-radius ' +
   'background-size transform'
  ).map( function(prop) {
    return v(prop).camelize();
  });

  CSS_TRANSITIONS_PROPERTIES.each(function(property) {
    S2.CSS.PROPERTIES.push(property);
  });

  S2.CSS.NUMERIC_PROPERTIES = S2.CSS.PROPERTIES.findAll(function(property) {
    return !property.endsWith('olor')
  });

  CSS_TRANSITIONS_HARDWARE_ACCELERATED_PROPERTIES =
   S2.Extensions.HardwareAcceleratedCSSTransitions ?
    $w('top left bottom right opacity') : [];

  var TRANSLATE_TEMPLATE = new Template("translate(#{0}px, #{1}px)");


  var Operators = FX.Operators;

  Operators.CssTransition = Class.create(Operators.Base, {
    initialize: function($super, effect, object, options) {
      $super(effect, object, options);
      this.element = $(this.object);

      var style = this.options.style;

      this.style = Object.isString(style) ?
       S2.CSS.normalize(this.element, style) : style;

      this.style = $H(this.style);

      this.targetStyle = S2.CSS.serialize(this.style);
      this.running = false;
    },

    _canHardwareAccelerate: function() {
      if (this.effect.options.accelerate === false) return false;

      var style = this.style.toObject(), keys = this.style.keys();
      var element = this.element;

      if (keys.length === 0) return false;

      var otherPropertyExists = keys.any( function(key) {
        return !CSS_TRANSITIONS_HARDWARE_ACCELERATED_PROPERTIES.include(key);
      });

      if (otherPropertyExists) return false;

      var currentStyles = {
        left:   element.getStyle('left'),
        right:  element.getStyle('right'),
        top:    element.getStyle('top'),
        bottom: element.getStyle('bottom')
      };

      function hasTwoPropertiesOnSameAxis(obj) {
        if (obj.top && obj.bottom) return true;
        if (obj.left && obj.right) return true;
        return false;
      }

      if (hasTwoPropertiesOnSameAxis(currentStyles)) return false;
      if (hasTwoPropertiesOnSameAxis(style))         return false;

      return true;
    },

    _adjustForHardwareAcceleration: function(style) {
      var dx = 0, dy = 0;

      $w('top bottom left right').each( function(prop) {
        if (!style.hasKey(prop)) return;
        var current = window.parseInt(this.element.getStyle(prop), 10);
        var target  = window.parseInt(style.get(prop), 10);

        if (prop === 'top') {
          dy += (target - current);
        } else if (prop === 'bottom') {
          dy += (current - target);
        } else if (prop === 'left') {
          dx += (target - current);
        } else if (prop === 'right') {
          dx += (current - target);
        }

        style.unset(prop);
      }, this);

      var transformProperty = v('transform');

      if (dx !== 0 || dy !== 0) {
        var translation = TRANSLATE_TEMPLATE.evaluate([dx, dy]);
        style.set(transformProperty, translation);
      }

      this.targetStyle += transformProperty + ': translate(0px, 0px);';
      return style;
    },

    render: function() {
      if (this.running === true) return;
      var style = this.style.clone(), effect = this.effect;
      if (this._canHardwareAccelerate()) {
        effect.accelerated = true;
        style = this._adjustForHardwareAcceleration(style);
      } else {
        effect.accelerated = false;
      }

      var s = this.element.style;

      var original = {};
      $w('transition-property transition-duration transition-timing-function').each( function(prop) {
        prop = v(prop).camelize();
        original[prop] = s[prop];
      });

      this.element.store('s2.targetStyle', this.targetStyle);

      this.element.store('s2.originalTransitionStyle', original);

      s[v('transition-property').camelize()] = style.keys().join(',');
      s[v('transition-duration').camelize()] = (effect.duration / 1000).toFixed(3) + 's';
      s[v('transition-timing-function').camelize()] = timingFunctionForTransition(effect.options.transition);

      if (Prototype.Browser.Opera) {
        this._setStyle.bind(this).defer(style.toObject());
      } else this._setStyle(style.toObject());
      this.running = true;

      this.render = Prototype.emptyFunction;
    },

    _setStyle: function(style) {
      this.element.setStyle(style);
    }
  });

  Operators.CssTransition.TIMING_MAP = {
    linear:     'linear',
    sinusoidal: 'ease-in-out'
  };

  function timingFunctionForTransition(transition) {
    var timing = null, MAP = FX.Operators.CssTransition.TIMING_MAP;

    for (var t in MAP) {
      if (FX.Transitions[t] === transition) {
        timing = MAP[t];
        break;
      }
    }
    return timing;
  }

  function isCSSTransitionCompatible(effect) {
    if (!S2.Extensions.CSSTransitions) return false;
    var opt = effect.options;

    if ((opt.engine || '') === 'javascript') return false;

    if (!timingFunctionForTransition(opt.transition)) return false;

    if (opt.propertyTransitions) return false;

    return true;
  };

  FX.Morph = Class.create(FX.Morph, {
    setup: function() {
      if (this.options.change) {
        this.setupWrappers();
      } else if (this.options.style) {
        this.engine  = 'javascript';
        var operator = 'style';

        var style = Object.isString(this.options.style) ?
         S2.CSS.parseStyle(this.options.style) : style;

        var normalizedStyle = S2.CSS.normalize(
         this.destinationElement || this.element, style);

        var changesStyle = Object.keys(normalizedStyle).length > 0;

        if (changesStyle && isCSSTransitionCompatible(this)) {
          this.engine = 'css-transition';
          operator    = 'CssTransition';

          this.update = function(position) {
            this.element.store('s2.effect', this);

            S2.FX.setReady(this.element, false);

            for (var i = 0, operator; operator = this.operators[i]; i++) {
              operator.render(position);
            }

            this.render = Prototype.emptyFunction;
          }
        }

        this.animate(operator, this.destinationElement || this.element, {
          style: this.options.style,
          propertyTransitions: this.options.propertyTransitions || { }
        });
      }
    }
  });

  document.observe(transitionEndEventName, function(event) {
    var element = event.element();
    if (!element) return;

    var effect = element.retrieve('s2.effect');

    if (!effect || effect.state === 'finished') return;

    function adjust(element, effect){
      var targetStyle = element.retrieve('s2.targetStyle');
      if (!targetStyle) return;

      element.setStyle(targetStyle);

      var originalTransitionStyle = element.retrieve('s2.originalTransitionStyle');

      var storage = element.getStorage();
      storage.unset('s2.targetStyle');
      storage.unset('s2.originalTransitionStyle');
      storage.unset('s2.effect');

      if (originalTransitionStyle) {
        element.setStyle(originalTransitionStyle);
      }
      S2.FX.setReady(element);
    }

    var durationProperty = v('transition-duration').camelize();
    element.style[durationProperty] = '';
    adjust(element, effect);

    effect.state = 'finished';

    var after = effect.options.after;
    if (after) after(effect);
  });
})(S2.FX);

Element.__scrollTo = Element.scrollTo;
Element.addMethods({
  scrollTo: function(element, to, options){
    if(arguments.length == 1) return Element.__scrollTo(element);
    new S2.FX.Scroll(element, Object.extend(options || {}, { to: to })).play();
    return element;
  }
});

Element.addMethods({
  effect: function(element, effect, options){
    if (Object.isFunction(effect))
      effect = new effect(element, options);
    else if (Object.isString(effect))
      effect = new S2.FX[effect.charAt(0).toUpperCase() + effect.substring(1)](element, options);
    effect.play(element, options);
    return element;
  },

  morph: function(element, style, options) {
    options = S2.FX.parseOptions(options);
    if (!options.queue) {
      options.queue = element.retrieve('S2.FX.Queue');
      if (!options.queue) {
        element.store('S2.FX.Queue', options.queue = new S2.FX.Queue());
      }
    }
    if (!options.position) options.position = 'end';
    return element.effect('morph', Object.extend(options, { style: style }));
  }.optionize(),

  appear: function(element, options){
    return element.setStyle('opacity: 0;').show().morph('opacity: 1', options);
  },

  fade: function(element, options){
    options = Object.extend({
      after: Element.hide.curry(element)
    }, options || {});
    return element.morph('opacity: 0', options);
  },

  cloneWithoutIDs: function(element) {
    element = $(element);
    var clone = element.cloneNode(true);
    clone.id = '';
    $(clone).select('*[id]').each(function(e) { e.id = ''; });
    return clone;
  }
});

(function(){
  var transform;

  if(window.CSSMatrix) transform = function(element, transform){
    element.style.transform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(window.WebKitCSSMatrix) transform = function(element, transform){
    element.style.webkitTransform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(Prototype.Browser.Gecko) transform = function(element, transform){
    element.style.MozTransform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(Prototype.Browser.IE) transform = function(element, transform){
    if(!element._oDims)
      element._oDims = [element.offsetWidth, element.offsetHeight];
    var c = Math.cos(transform.rotation||0) * 1, s = Math.sin(transform.rotation||0) * 1,
        filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand',M11="+c+",M12="+(-s)+",M21="+s+",M22="+c+")";
    element.style.filter = filter;
    element.style.marginLeft = (element._oDims[0]-element.offsetWidth)/2+'px';
    element.style.marginTop = (element._oDims[1]-element.offsetHeight)/2+'px';
    return element;
  };
  else transform = function(element){ return element; }

  Element.addMethods({ transform: transform });
})();

S2.viewportOverlay = function(){
  var viewport = document.viewport.getDimensions(),
    offsets = document.viewport.getScrollOffsets();
  return new Element('div').setStyle({
    position: 'absolute',
    left: offsets.left + 'px', top: offsets.top + 'px',
    width: viewport.width + 'px', height: viewport.height + 'px'
  });
};
S2.FX.Helpers = {
  fitIntoRectangle: function(w, h, rw, rh){
    var f = w/h, rf = rw/rh; return f < rf ?
      [(rw - (w*(rh/h)))/2, 0, w*(rh/h), rh] :
      [0, (rh - (h*(rw/w)))/2, rw, h*(rw/w)];
  }
};

S2.FX.Operators.Zoom = Class.create(S2.FX.Operators.Style, {
  initialize: function($super, effect, object, options) {
    var viewport = document.viewport.getDimensions(),
      offsets = document.viewport.getScrollOffsets(),
      dims = object.getDimensions(),
      f = S2.FX.Helpers.fitIntoRectangle(dims.width, dims.height,
      viewport.width - (options.borderWidth || 0)*2,
      viewport.height - (options.borderWidth || 0)*2);

    Object.extend(options, { style: {
      left: f[0] + (options.borderWidth || 0) + offsets.left + 'px',
      top: f[1] + (options.borderWidth || 0) + offsets.top + 'px',
      width: f[2] + 'px', height: f[3] + 'px'
    }});
    $super(effect, object, options);
  }
});

S2.FX.Zoom = Class.create(S2.FX.Element, {
  setup: function() {
    this.clone = this.element.cloneWithoutIDs();
    this.element.insert({before:this.clone});
    this.clone.absolutize().setStyle({zIndex:9999});

    this.overlay = S2.viewportOverlay();
    if (this.options.overlayClassName)
      this.overlay.addClassName(this.options.overlayClassName)
    else
      this.overlay.setStyle({backgroundColor: '#000', opacity: '0.9'});
    $$('body')[0].insert(this.overlay);

    this.animate('zoom', this.clone, {
      borderWidth: this.options.borderWidth,
      propertyTransitions: this.options.propertyTransitions || { }
    });
  },
  teardown: function() {
    this.clone.observe('click', function() {
      this.overlay.remove();
      this.clone.morph('opacity:0', {
        duration: 0.2,
        after: function() {
          this.clone.remove();
        }.bind(this)
      })
    }.bind(this));
  }
});
