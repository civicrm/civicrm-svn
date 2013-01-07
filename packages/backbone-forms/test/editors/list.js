;(function(Form, Field, editors) {

    

module('List', {
    setup: function() {
        this.sinon = sinon.sandbox.create();
    },

    teardown: function() {
        this.sinon.restore();
    }
});

(function() {
    var Post = Backbone.Model.extend({
        defaults: {
            title: 'Danger Zone!',
            content: 'I love my turtleneck',
            author: 'Sterling Archer',
            slug: 'danger-zone',
            weapons: ['uzi', '9mm', 'sniper rifle']
        },
        
        schema: {
            title:      { type: 'Text' },
            content:    { type: 'TextArea' },
            author:     {},
            slug:       {},
            weapons:    'List'
        }
    });

    var List = editors.List;

    test('Default settings', function() {
        var list = new List();

        same(list.Editor, editors.Text);
    });

    test('Uses custom list editors if defined', function() {
        var list = new List({
            schema: { itemType: 'Object' }
        });

        same(list.Editor, editors.List.Object);
    });

    test('Uses regular editor if there is no list version', function() {
        var list = new List({
            schema: { itemType: 'Number' }
        });

        same(list.Editor, editors.Number);
    });

    test('Default value', function() {
        var list = new List().render();

        same(list.getValue(), []);
    });

    test('Custom value', function() {
        var list = new List({
            schema: { itemType: 'Number' },
            value: [1,2,3]
        }).render();

        same(list.getValue(), [1,2,3]);
    });

    test('Value from model', function() {
        var list = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        same(list.getValue(), ['uzi', '9mm', 'sniper rifle']);
    });

    test('setValue() - updates input value', function() {
        var list = new List().render();

        list.setValue(['a', 'b', 'c']);

        same(list.getValue(), ['a', 'b', 'c']);
    });

    test('validate() - returns validation errors', function() {
        var list = new List({
            schema: { validators: ['required', 'email'] },
            value: ['invalid', 'john@example.com', '', 'ok@example.com']
        }).render();

        var err = list.validate();

        same(err.type, 'list');
        same(err.errors[0].type, 'email');
        same(err.errors[1], null);
        same(err.errors[2].type, 'required');
        same(err.errors[3], null);
    });

    test('validate() - returns null if there are no errors', function() {
        var list = new List({
            schema: { validators: ['required', 'email'] },
            value: ['john@example.com', 'ok@example.com']
        }).render();

        var errs = list.validate();

        same(errs, null);
    });

    test('event: clicking something with data-action="add" adds an item', function() {
        var list = new List().render();

        same(list.items.length, 1);

        list.$('[data-action="add"]').click();
        
        same(list.items.length, 2);
    });

    test('render() - sets the $list property to the template {{items}} tag', function() {
        //Backup original template
        var _template = Form.templates.list;

        Form.setTemplates({
            list: '<ul class="customList">{{items}}</div>'
        });

        var list = new List().render();

        ok(list.$list.hasClass('customList'));

        //Restore template
        Form.templates.list = _template;
    });

    test('render() - creates items for each item in value array', function() {
        var list = new List({
            value: [1,2,3]
        });

        same(list.items.length, 0);

        list.render();

        same(list.items.length, 3);
    });

    test('render() - creates an initial empty item for empty array', function() {
        var list = new List({
            value: []
        });

        same(list.items.length, 0);

        list.render();

        same(list.items.length, 1);
    });

    test('addItem() - with no value', function() {
        var list = new List().render();

        var spy = this.sinon.spy(List, 'Item');

        list.addItem();

        var expectedOptions = {
            list: list,
            schema: list.schema,
            value: undefined,
            Editor: editors.Text,
            key: list.key
        }

        var actualOptions = spy.lastCall.args[0];

        same(spy.callCount, 1);
        same(list.items.length, 2);
        same(_.last(list.items).value, undefined);

        //Test options
        same(actualOptions, expectedOptions);
    });

    test('addItem() - with value', function() {
        var list = new List().render();

        var spy = this.sinon.spy(List, 'Item');

        list.addItem('foo');

        var expectedOptions = {
            list: list,
            schema: list.schema,
            value: 'foo',
            Editor: editors.Text,
            key: list.key
        }

        var actualOptions = spy.lastCall.args[0];

        same(spy.callCount, 1);
        same(actualOptions, expectedOptions);
        same(list.items.length, 2);
        same(_.last(list.items).value, 'foo');
    });

    test('addItem() - adds the item to the DOM', function() {
        var list = new List().render();

        list.addItem('foo');

        var $el = list.$('li:last input');

        same($el.val(), 'foo');
    });

    test('removeItem() - removes passed item from view and item array', function() {
        var list = new List().render();

        list.addItem();

        same(list.items.length, 2);
        same(list.$('li').length, 2);

        var item = _.last(list.items);

        list.removeItem(item);

        same(list.items.length, 1);
        same(list.$('li').length, 1);
        same(_.indexOf(list.items, item), -1, 'Removed item is no longer in list.items');
    });

    test('removeItem() - adds an empty item if list is empty', function() {
        var list = new List().render();

        var spy = sinon.spy(list, 'addItem');

        list.removeItem(list.items[0]);

        same(spy.callCount, 1);
        same(list.items.length, 1);
    });

    test('removeItem() - can be configured to ask for confirmation - and is cancelled', function() {
        //Simulate clicking 'cancel' on confirm dialog
        var stub = this.sinon.stub(window, 'confirm', function() {
            return false;
        });

        var list = new List({
            schema: {
                confirmDelete: 'You sure about this?'
            }
        }).render();

        list.addItem();
        list.removeItem(_.last(list.items));

        //Check confirmation was shown
        same(stub.callCount, 1);

        //With custom message
        var confirmMsg = stub.lastCall.args[0];
        same(confirmMsg, 'You sure about this?')

        //And item was not removed
        same(list.items.length, 2, 'Did not remove item');
    });

    test('removeItem() - can be configured to ask for confirmation - and is confirmed', function() {
        //Simulate clicking 'ok' on confirm dialog
        var stub = this.sinon.stub(window, 'confirm', function() {
            return true;
        });

        var list = new List({
            schema: {
                confirmDelete: 'You sure about this?'
            }
        }).render();

        list.addItem();
        list.removeItem(_.last(list.items));

        //Check confirm was shown
        same(stub.callCount, 1);

        //And item was removed
        same(list.items.length, 1, 'Removed item');
    });
    
    test("focus() - gives focus to editor and its first item's editor", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        stop();
        setTimeout(function() {
          ok(field.items[0].editor.hasFocus);
          ok(field.hasFocus);

          start();
        }, 0);
    });

    test("focus() - triggers the 'focus' event", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();

        field.on('focus', spy);

        field.focus();

        stop();
        setTimeout(function() {
          ok(spy.called);
          ok(spy.calledWith(field));

          start();
        }, 0);
    });

    test("blur() - removes focus from the editor and its first item's editor", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        field.blur();

        stop();
        setTimeout(function() {
          ok(!field.items[0].editor.hasFocus);
          ok(!field.hasFocus);

          start();
        }, 0);
    });

    test("blur() - triggers the 'blur' event", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        var spy = this.sinon.spy();

        field.on('blur', spy);

        field.blur();

        stop();
        setTimeout(function() {
          ok(spy.called);
          ok(spy.calledWith(field));

          start();
        }, 0);
    });

    test("'change' event - bubbles up from item's editor", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();

        field.on('change', spy);

        field.items[0].editor.trigger('change', field.items[0].editor);

        ok(spy.called);
        ok(spy.calledWith(field));
    });
    
    test("'change' event - is triggered when an item is added", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();
        
        field.on('change', spy);

        var item = field.addItem(null, true);

        ok(spy.called);
        ok(spy.calledWith(field));
    });
    
    test("'change' event - is triggered when an item is removed", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();
        
        item = field.items[0];

        field.on('change', spy);

        field.removeItem(item);

        ok(spy.called);
        ok(spy.calledWith(field));
    });

    test("'focus' event - bubbles up from item's editor when editor doesn't have focus", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();

        field.on('focus', spy);

        field.items[0].editor.focus();

        ok(spy.called);
        ok(spy.calledWith(field));
    });

    test("'focus' event - doesn't bubble up from item's editor when editor already has focus", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        var spy = this.sinon.spy();

        field.on('focus', spy);

        field.items[0].editor.focus();

        ok(!spy.called);
    });

    test("'blur' event - bubbles up from item's editor when editor has focus and we're not focusing on another one of the editor's item's editors", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        var spy = this.sinon.spy();

        field.on('blur', spy);

        field.items[0].editor.blur();

        stop();
        setTimeout(function() {
            ok(spy.called);
            ok(spy.calledWith(field));

            start();
        }, 0);
    });

    test("'blur' event - doesn't bubble up from item's editor when editor has focus and we're focusing on another one of the editor's item's editors", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        field.focus();

        var spy = this.sinon.spy();

        field.on('blur', spy);

        field.items[0].editor.blur();
        field.items[1].editor.focus();

        stop();
        setTimeout(function() {
            ok(!spy.called);

            start();
        }, 0);
    });

    test("'blur' event - doesn't bubble up from item's editor when editor doesn't have focus", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();

        field.on('blur', spy);

        field.items[0].editor.blur();

        stop();
        setTimeout(function() {
            ok(!spy.called);

            start();
        }, 0);
    });
    
    test("'add' event - is triggered when an item is added", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();
        
        field.on('add', spy);

        var item = field.addItem(null, true);

        ok(spy.called);
        ok(spy.calledWith(field, item.editor));
    });
    
    test("'remove' event - is triggered when an item is removed", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var item = field.items[0];

        var spy = this.sinon.spy();

        field.on('remove', spy);

        field.removeItem(item);

        ok(spy.called);
        ok(spy.calledWith(field, item.editor));
    });

    test("Events bubbling up from item's editors", function() {
        var field = new List({
            model: new Post,
            key: 'weapons'
        }).render();

        var spy = this.sinon.spy();

        field.on('item:whatever', spy);

        field.items[0].editor.trigger('whatever', field.items[0].editor);

        ok(spy.called);
        ok(spy.calledWith(field, field.items[0].editor));
    });
})();



module('List.Item', {
    setup: function() {
        this.sinon = sinon.sandbox.create();
    },

    teardown: function() {
        this.sinon.restore();
    }
});

(function() {
    var List = editors.List;

    test('render() - creates the editor for the given listType', function() {
        var spy = this.sinon.spy(editors, 'Number');

        var list = new List({
            schema: { itemType: 'Number' }
        }).render();

        var item = new List.Item({
            list: list,
            value: 123,
            Editor: editors.Number
        }).render();

        //Check created correct editor
        var editorOptions = spy.lastCall.args[0];

        same(editorOptions, {
            key: '',
            schema: item.schema,
            value: 123,
            list: list,
            item: item,
            key: item.key
        });
    });

    test('render() - creates the main element entirely from template, with editor in {{editor}} tag location', function() {
        //Replace template
        var _template = Form.templates.listItem;

        Form.setTemplates({
            listItem: '<div class="outer"><div class="inner">{{editor}}</div></div>'
        })

        //Create item
        var item = new List.Item({ list: new List }).render();

        //Check there is no wrapper tag
        ok(item.$el.hasClass('outer'));

        //Check editor placed in correct location
        ok(item.editor.$el.parent().hasClass('inner'));

        //Restore template
        Form.templates.listItem = _template;
    });

    test('getValue() - returns editor value', function() {
        var item = new List.Item({
            list: new List,
            value: 'foo'
        }).render();

        same(item.editor.getValue(), 'foo');
        same(item.getValue(), 'foo');
    });

    test('setValue() - sets editor value', function() {
        var item = new List.Item({ list: new List }).render();

        item.setValue('woo');

        same(item.editor.getValue(), 'woo');
        same(item.getValue(), 'woo');
    });

    test('remove() - removes the editor then itself', function() {
        var item = new List.Item({ list: new List }).render();

        var editorSpy = this.sinon.spy(item.editor, 'remove'),
            viewSpy = this.sinon.spy(Backbone.View.prototype.remove, 'call');

        item.remove();

        //Check removed editor
        ok(editorSpy.calledOnce, 'Called editor remove');

        //Check removed main item
        ok(viewSpy.calledWith(item), 'Called parent view remove');
    });

    test('validate() - invalid - calls setError and returns error', function() {
        var item = new List.Item({
            list: new List({
                schema: { validators: ['required', 'email'] }
            }),
            value: 'invalid'
        }).render();

        var spy = this.sinon.spy(item, 'setError');

        var err = item.validate();

        same(err.type, 'email');
        same(spy.callCount, 1, 'Called setError');
        same(spy.lastCall.args[0], err, 'Called with error');
    });

    test('validate() - valid - calls clearError and returns null', function() {
        var item = new List.Item({
            list: new List({
                schema: { validators: ['required', 'email'] }
            }),
            value: 'valid@example.com'
        }).render();

        var spy = this.sinon.spy(item, 'clearError');

        var err = item.validate();

        same(err, null);
        same(spy.callCount, 1, 'Called clearError');
    });

    test('setError()', function() {
        var item = new List.Item({ list: new List }).render();

        item.setError({ type: 'errType', message: 'ErrMessage' });

        ok(item.$el.hasClass(Form.classNames.error), 'Element has error class');
        same(item.$el.attr('title'), 'ErrMessage');
    });

    test('clearError()', function() {
        var item = new List.Item({ list: new List }).render();

        item.setError({ type: 'errType', message: 'ErrMessage' });

        item.clearError();

        same(item.$el.hasClass(Form.classNames.error), false, 'Error class is removed from element');
        same(item.$el.attr('title'), undefined);
    });
})();



// Needs a editors.List.Modal.ModalAdapter that isn't dependent on Bootstrap.

// module('List.Modal', {
//     setup: function() {
//         this.sinon = sinon.sandbox.create();
//     },
// 
//     teardown: function() {
//         this.sinon.restore();
//     }
// });
// 
// (function() {
// 
//   var editor = editors.List.Modal,
//       schema = {
//           itemType: "Object",
//           subSchema: {
//               id: { type: 'Number' },
//               name: { }
//           }
//       };
//   
//   test("focus() - gives focus to the editor and opens the modal", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
// 
//       field.focus();
// 
//       ok(field.modal);
//       ok(field.hasFocus);
//   });
// 
//   test("focus() - triggers the 'focus' event", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
// 
//       var spy = this.sinon.spy();
// 
//       field.on('focus', spy);
// 
//       field.focus();
//       
//       ok(spy.called);
//       ok(spy.calledWith(field));
//   });
//   
//   test("blur() - removes focus from the editor and closes the modal", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
// 
//       field.focus();
//       
//       field.blur()
// 
//       ok(!field.modal);
//       ok(!field.hasFocus);
//   });
//   
//   test("blur() - triggers the 'blur' event", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       field.focus();
// 
//       var spy = this.sinon.spy();
// 
//       field.on('blur', spy);
// 
//       field.blur();
//       
//       ok(spy.called);
//       ok(spy.calledWith(field));
//   });
//   
//   test("'change' event - is triggered when the modal is submitted", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       field.openEditor();
// 
//       var spy = this.sinon.spy();
//       
//       field.on('blur', spy);
//       
//       field.modal.trigger('ok');
//       field.modal.close();
//       
//       ok(spy.calledOnce);
//       ok(spy.alwaysCalledWith(field));
//   });
//   
//   test("'focus' event - is triggered when the modal is opened", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       var spy = this.sinon.spy();
//       
//       field.on('focus', spy);
//       
//       field.openEditor();
//       
//       ok(spy.calledOnce);
//       ok(spy.alwaysCalledWith(field));
//   });
//   
//   test("'blur' event - is triggered when the modal is closed", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       field.openEditor();
// 
//       var spy = this.sinon.spy();
//       
//       field.on('blur', spy);
//       
//       field.modal.trigger('cancel');
//       field.modal.close();
//       
//       ok(spy.calledOnce);
//       ok(spy.alwaysCalledWith(field));
//   });
//   
//   test("'open' event - is triggered when the modal is opened", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       var spy = this.sinon.spy();
//       
//       field.on('open', spy);
//       
//       field.openEditor();
//       
//       ok(spy.calledOnce);
//       ok(spy.alwaysCalledWith(field));
//   });
//   
//   test("'close' event - is triggered when the modal is closed", function() {
//       var field = new editor({
//           schema: schema
//       }).render();
//       
//       field.openEditor();
// 
//       var spy = this.sinon.spy();
//       
//       field.on('close', spy);
//       
//       field.modal.trigger('cancel');
//       field.modal.close();
//       
//       ok(spy.calledOnce);
//       ok(spy.alwaysCalledWith(field));
//   });
//   
// })();

})(Backbone.Form, Backbone.Form.Field, Backbone.Form.editors);
