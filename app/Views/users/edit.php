<h1>Editar Usuario</h1>
<form method="POST" action="/users/<?php echo $user->id; ?>">
    <input type="hidden" name="_method" value="PUT">
    
    <?php if (isset($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $field => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <li><?php echo $message; ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo $old['name'] ?? $user->name; ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo $old['email'] ?? $user->email; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
    <a href="/users/<?php echo $user->id; ?>" class="btn btn-secondary">Cancelar</a>
</form>