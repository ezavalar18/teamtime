✅ PASOS PARA CLONAR UN PROYECTO DE GITHUB EN LINUX
Abre una terminal en Linux.

Asegúrate de tener git instalado:

bash
Copiar
Editar
git --version
Si no está instalado:

bash
Copiar
Editar
sudo apt update
sudo apt install git
Ve al directorio donde quieres clonar el proyecto:

bash
Copiar
Editar
cd ~/Documentos  # o donde quieras
Clona el repositorio:

bash
Copiar
Editar
git clone https://github.com/usuario/repositorio.git
Ejemplo:

bash
Copiar
Editar
git clone https://github.com/ezavalar18/teamtime.git
Entra al directorio clonado:

bash
Copiar
Editar
cd teamtime


--------------------------------------------------------------------

✅ PASOS PARA CLONAR UN PROYECTO DE GITHUB EN WINDOWS
Abre Git Bash o CMD (preferible Git Bash).

Verifica que tienes git instalado:

bash
Copiar
Editar
git --version
Ve al directorio donde quieres clonar el proyecto:

bash
Copiar
Editar
cd ~/Downloads
Clona el mismo repositorio:

bash
Copiar
Editar
git clone https://github.com/ezavalar18/teamtime.git
Ingresa al proyecto:

bash
Copiar
Editar
cd teamtime
🔄 CONSEJOS PARA TRABAJAR EN AMBOS SISTEMAS
Evita conflictos por saltos de línea (CRLF vs LF):
Puedes configurar Git para que maneje esto automáticamente:

bash
Copiar
Editar
git config --global core.autocrlf true   # En Windows
git config --global core.autocrlf input  # En Linux
Sincroniza siempre antes de trabajar:

bash
Copiar
Editar
git pull origin main   # o master, según tu rama
Antes de subir cambios:

bash
Copiar
Editar
git add .
git commit -m "Mensaje claro de los cambios"
git push origin main   # o master
