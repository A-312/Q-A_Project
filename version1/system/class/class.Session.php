<?php
class Session implements SessionHandlerInterface {
	const LIFETIME = 86400; // 3600 * 24

	public function open($savePath, $sessionName) {
		$this->gc();

		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		global $BasedeDonnee;
		return $BasedeDonnee->lire("SELECT data FROM session WHERE id = ?", array($id), "data");
	}

	public function write($id, $data) {
		global $BasedeDonnee;
		$expire = intval(time() + Session::LIFETIME);

		if($BasedeDonnee->lire("SELECT COUNT(*) AS nbre_entrees FROM session WHERE id = ?", array($id), "nbre_entrees") == 0) {
			$BasedeDonnee->ecrire("INSERT INTO session (id, data, expire) VALUES (?, ?, ?)", array($id, $data, $expire));
        } else {
			$BasedeDonnee->ecrire("UPDATE session SET data = ?, expire = ? WHERE id = ?", array($data, $expire, $id));
        }
        return true;
	}

	public function destroy($id) {
		global $BasedeDonnee;
		$BasedeDonnee->ecrire("DELETE FROM session WHERE id = ?", array($id));

		return true;
	}

	public function gc($maxlifetime = 0) {
		global $BasedeDonnee;
		$BasedeDonnee->ecrire("DELETE FROM session WHERE expire < ?", array(time()));

		return true;
	}
}
?>