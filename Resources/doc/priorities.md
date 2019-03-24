# Compiler priorities
Symbok compiler will try to find how method will be generated, ie with which configurations.
Each method configuration will be computed using following priorities.

## Constructor parameter nullable
Constructor parameter nullable configuration will be used to know if constructor parameter will be nullable.

1. [Nullable annotation](annotations/nullable.md)
2. [Doctrine column annotation](doctrine.md#doctrines-column-annotation)
3. [AllArgsConstructor annotation nullable parameter](annotations/allArgsConstructor.md)
4. `symbok.defaults.nullable.constructor` config value

## Getters/Setters nullable
Getters/Setters nullable configuration will be used to know if getters/setters will return/use nullable values/parameters.

1. [Getter/Setter annotation nullable parameter](annotations/getter.md)
2. [Nullable annotation](annotations/nullable.md)
3. [Doctrine column annotation](doctrine.md#doctrines-column-annotation)
4. [Data annotation nullable parameter](annotations/data.md)
5. `symbok.defaults.nullable.getter_setter` config value

## Fluent setters
Fluent setters configuration will be used to know if setters will have to return self class instance.

1. [Setter annotation fluent parameter](annotations/setter.md)
2. [Data annotation fluentSetters parameter](annotations/data.md)
3. `symbok.defaults.fluent_setters` config value

## Property type
Last but not least, property type configuration will be used to know which type the property is.

1. Property `@var` tag in docblock
2. [Doctrine relation annotation](doctrine.md#doctrine-entity-relations)
3. [Doctrine column annotation](doctrine.md#doctrines-column-annotation)
4. `mixed` type will be used
