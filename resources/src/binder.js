/**
 * A small Alpine-like binder for the player skins.
 *
 * The skins were designed with Alpine.js. The plugin cannot ship Alpine itself: themes that already
 * run Alpine would get two instances (alpine:init fires twice, x-data trees get bound twice).
 * This binder only reads `ars-` attributes, so it never touches a theme's Alpine markup:
 *
 *   ars-text="expr"                 textContent
 *   ars-show="expr"                 display: none when falsy; honors ars-transition:enter / enter-start / enter-end /
 *                                   leave / leave-start / leave-end classes like x-transition
 *   ars-bind:attr="expr"            attribute (class and style are merged with the static ones; value is a property)
 *   ars-on:event.mods="statements"  listener; mods: prevent, stop, window, self, and keys (escape, enter, space, up, down, left, right)
 *   ars-for="item in list"          on <template>; also "(item, index) in list"
 *   ars-data="{ … }"                local state for the element and its children
 *   ars-cloak                       removed once bound (hide it with CSS until then)
 *
 * Expressions run with `with (scope)`, where the scope chains local state → the player store → helpers,
 * plus $event and $el. Everything re-renders on each store update; the store emits at most a few times a second.
 */

const KEYS = { escape: 'Escape', enter: 'Enter', space: ' ', up: 'ArrowUp', down: 'ArrowDown', left: 'ArrowLeft', right: 'ArrowRight' };
const compiled = new Map();

function compile(expression, statement = false) {
  const key = (statement ? 's:' : 'e:') + expression;

  if (!compiled.has(key)) {
    const body = statement ? `with ($scope) { ${expression} }` : `with ($scope) { return (${expression}); }`;
    // eslint-disable-next-line no-new-func
    compiled.set(key, new Function('$scope', '$event', '$el', body));
  }

  return compiled.get(key);
}

/** A scope that reads and writes its own keys, else the parent's. */
function childScope(locals, parent) {
  return new Proxy(locals, {
    has: (target, key) => typeof key === 'string' && !key.startsWith('$') && (key in target || key in parent),
    get: (target, key) => (key in target ? target[key] : parent[key]),
    set: (target, key, value) => {
      if (key in target || !(key in parent)) target[key] = value;
      else parent[key] = value;
      return true;
    },
  });
}

function classList(value) {
  if (!value) return [];
  if (typeof value === 'string') return value.split(/\s+/).filter(Boolean);
  if (Array.isArray(value)) return value.flatMap(classList);

  return Object.keys(value).filter((name) => value[name]).flatMap(classList);
}

function transitionTime(element) {
  const style = getComputedStyle(element);
  const longest = (list) => Math.max(...list.split(',').map((part) => parseFloat(part) * (part.includes('ms') ? 1 : 1000) || 0));

  return longest(style.transitionDuration) + longest(style.transitionDelay);
}

/** Show or hide an element, running x-transition style classes when it has them. */
function toggle(element, show, first) {
  const phase = show ? 'enter' : 'leave';
  const classes = (name) => classList(element.getAttribute(`ars-transition:${name}`));

  element.arsTransition = (element.arsTransition || 0) + 1;
  const run = element.arsTransition;

  if (first || !element.hasAttribute('ars-transition:enter')) {
    element.style.display = show ? '' : 'none';
    return;
  }

  const all = ['enter', 'enter-start', 'enter-end', 'leave', 'leave-start', 'leave-end'].flatMap(classes);
  element.classList.remove(...all);
  if (show) element.style.display = '';
  element.classList.add(...classes(phase), ...classes(`${phase}-start`));

  requestAnimationFrame(() => requestAnimationFrame(() => {
    if (run !== element.arsTransition) return;
    element.classList.remove(...classes(`${phase}-start`));
    element.classList.add(...classes(`${phase}-end`));

    setTimeout(() => {
      if (run !== element.arsTransition) return;
      element.classList.remove(...classes(phase), ...classes(`${phase}-end`));
      if (!show) element.style.display = 'none';
    }, transitionTime(element));
  }));
}

export class Binder {
  /**
   * @param {Element} root Element whose subtree is bound.
   * @param {object} scope Base scope (the store, wrapped to expose helpers).
   * @param {() => void} onChange Called after an event handler ran, to re-render.
   */
  constructor(root, scope, onChange) {
    this.root = root;
    this.onChange = onChange;
    this.bindings = [];
    this.walk(root, scope, this.bindings);
    this.render();
  }

  render(bindings = this.bindings) {
    for (const binding of bindings) {
      try {
        binding.update();
      } catch (error) {
        console.warn('[a-ripple-song player]', error);
      }
    }
  }

  walk(element, scope, bindings) {
    if (element.hasAttribute('ars-data')) {
      scope = childScope(compile(element.getAttribute('ars-data')).call(element, scope, undefined, element) || {}, scope);
    }

    if (element.tagName === 'TEMPLATE' && element.hasAttribute('ars-for')) {
      bindings.push(this.loop(element, scope));
      return;
    }

    for (const { name, value } of [...element.attributes]) {
      if (name === 'ars-text') bindings.push(this.text(element, scope, value));
      else if (name === 'ars-show') bindings.push(this.show(element, scope, value));
      else if (name.startsWith('ars-bind:')) bindings.push(this.bind(element, scope, name.slice(9), value));
      else if (name.startsWith('ars-on:')) this.on(element, scope, name.slice(7), value);
    }
    element.removeAttribute('ars-cloak');

    for (const child of [...element.children]) this.walk(child, scope, bindings);
  }

  evaluate(expression, scope, element) {
    return compile(expression).call(element, scope, undefined, element);
  }

  text(element, scope, expression) {
    return {
      update: () => {
        const value = this.evaluate(expression, scope, element);
        const text = value === undefined || value === null ? '' : String(value);
        if (element.textContent !== text) element.textContent = text;
      },
    };
  }

  show(element, scope, expression) {
    let shown;

    return {
      update: () => {
        const visible = !!this.evaluate(expression, scope, element);
        if (visible === shown) return;
        toggle(element, visible, shown === undefined);
        shown = visible;
      },
    };
  }

  bind(element, scope, attribute, expression) {
    let added = [];

    return {
      update: () => {
        const value = this.evaluate(expression, scope, element);

        if (attribute === 'class') {
          const next = classList(value);
          element.classList.remove(...added.filter((name) => !next.includes(name)));
          element.classList.add(...next);
          added = next;
        } else if (attribute === 'style') {
          for (const declaration of String(value || '').split(';')) {
            const index = declaration.indexOf(':');
            if (index > 0) element.style.setProperty(declaration.slice(0, index).trim(), declaration.slice(index + 1).trim());
          }
        } else if (attribute === 'value') {
          // Do not fight the user while they drag a slider.
          const dragging = element.type === 'range' && element.matches(':active');
          if (!dragging && String(element.value) !== String(value ?? '')) element.value = value ?? '';
        } else if (attribute.startsWith('aria-') && typeof value === 'boolean') {
          // ARIA states are the strings "true" / "false", like Alpine renders them.
          element.setAttribute(attribute, String(value));
        } else if (value === null || value === undefined || value === false) {
          element.removeAttribute(attribute);
        } else {
          const text = value === true ? '' : String(value);
          if (element.getAttribute(attribute) !== text) element.setAttribute(attribute, text);
        }
      },
    };
  }

  on(element, scope, descriptor, statement) {
    const [event, ...modifiers] = descriptor.split('.');
    const keys = modifiers.filter((modifier) => KEYS[modifier]).map((modifier) => KEYS[modifier]);
    const target = modifiers.includes('window') ? window : modifiers.includes('document') ? document : element;
    const handler = compile(statement, true);

    target.addEventListener(event, ($event) => {
      if (keys.length && !keys.includes($event.key)) return;
      if (modifiers.includes('self') && $event.target !== element) return;
      if (modifiers.includes('prevent')) $event.preventDefault();
      if (modifiers.includes('stop')) $event.stopPropagation();
      handler.call(element, scope, $event, element);
      this.onChange();
    }, { passive: event === 'wheel' && !modifiers.includes('prevent') ? true : undefined });
  }

  loop(template, scope) {
    const match = template.getAttribute('ars-for').match(/^\s*(?:\(\s*(\w+)\s*(?:,\s*(\w+))?\s*\)|(\w+))\s+in\s+(.+)$/);
    const [, itemName = match[3], indexName, , listExpression] = match;
    let rendered = [];
    let key = null;

    return {
      update: () => {
        const list = [...(this.evaluate(listExpression, scope, template) || [])];
        const nextKey = list.map((item) => (item && item.id !== undefined ? item.id : item)).join('|');

        if (nextKey !== key) {
          key = nextKey;
          rendered.forEach(({ nodes }) => nodes.forEach((node) => node.remove()));
          rendered = list.map((item, index) => {
            const fragment = template.content.cloneNode(true);
            const nodes = [...fragment.children];
            const locals = { [itemName]: item };
            if (indexName) locals[indexName] = index;
            const itemScope = childScope(locals, scope);
            const bindings = [];
            nodes.forEach((node) => this.walk(node, itemScope, bindings));
            return { nodes, bindings };
          });
          template.after(...rendered.flatMap(({ nodes }) => nodes));
        }

        rendered.forEach(({ bindings }) => this.render(bindings));
      },
    };
  }
}
