
env_vars = {}

def set_variable(var_name, var_value):
    env_vars[var_name] = var_value

def get_variable(var_name):
    return env_vars.get(var_name, "Variable not found")

