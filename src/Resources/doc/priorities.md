# Compiler priorities
Symbok compiler has to figure out how method will be generated.
To do so, it will use *configurations*.

Each configuration is computed following these priorities.

## Constructor's parameter nullable
Used to know if a constructor parameter will be nullable and set to `null` by default.

1. [AllArgsConstructor annotation nullable parameter](annotations/allArgsConstructor.md)
2. [Data annotation nullable parameter](annotations/data.md)
3. [Property's Nullable annotation](annotations/nullable.md)
4. [Property's Doctrine `Column` annotation nullable paramater](doctrine.md#doctrine-column-annotation)
5. [Property's Doctrine relation annotation](doctrine.md#doctrine-entity-relations)
6. `symbok.defaults.constructor.nullable` config value
7. `true`

## Getters' return nullable
Used to know if getters will return nullable values.

1. [Getter annotation nullable parameter](annotations/getter.md)
2. Property's Doctrine `Column` annotation presence. In fact, in case of doctrine property, Getter
   methods should always return nullables values because even though these are
   required in the database, they may be not set yet.
3. [Nullable annotation](annotations/nullable.md)
4. [Doctrine `Column` annotation nullable parameter](doctrine.md#doctrine-column-annotation)
5. [Doctrine relation annotation](doctrine.md#doctrine-entity-relations)
6. [Data annotation nullable parameter](annotations/data.md)
7. `symbok.defaults.getter.nullable` config value
8. `true`

## Setters' parameter nullable
Used to know if setters will use nullable parameters.

1. [Setter annotation nullable parameter](annotations/setter.md)
2. Property's Doctrine `ManyToOne` relation presence. In fact, setter should
   always be nullable for a `ManyToOne` relation.
3. [Nullable annotation](annotations/nullable.md)
4. [Doctrine `Column` annotation nullable parameter](doctrine.md#doctrine-column-annotation)
5. [Doctrine relation annotation](doctrine.md#doctrine-entity-relations)
6. [Data annotation nullable parameter](annotations/data.md)
7. `symbok.defaults.setter.nullable` config value
8. `true`

## Fluent setters
Used to know if setters will have to return self class instance.

1. [Setter annotation fluent parameter](annotations/setter.md)
2. [Data annotation fluent parameter](annotations/data.md)
3. `symbok.defaults.setter.fluent` config value
8. `true`

## Property type
Used to know which type the property is.

1. Property `@var` tag in docblock
2. [Doctrine relation annotation](doctrine.md#doctrine-entity-relations)
3. [Doctrine column annotation](doctrine.md#doctrine-column-annotation)
4. `mixed`
