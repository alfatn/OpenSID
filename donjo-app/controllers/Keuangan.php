<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Keuangan extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		session_start();
		$this->load->model('keuangan_model');
		$this->load->model('header_model');
		$this->modul_ini = 201;
	}

	public function setdata_laporan($tahun, $semester)
	{
		$sess = array(
			'set_tahun' => $tahun,
			'set_semester' => $semester
		);
		$this->session->set_userdata( $sess );
		echo json_encode(true);
	}

	public function laporan()
	{
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();

		if (!empty($data['tahun_anggaran']))
		{
			redirect("keuangan/grafik/rincian_realisasi");
		}
		else
		{
			$_SESSION['success'] = -1;
			$_SESSION['error_msg'] = "Data Laporan Keuangan Belum Tersedia";
			redirect("keuangan/impor_data");
		}
	}

	public function grafik($jenis)
	{
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$tahun = $this->session->userdata('set_tahun') ? $this->session->userdata('set_tahun') : $data['tahun_anggaran'][0];
		$semester = $this->session->userdata('set_semester') ? $this->session->userdata('set_semester') : 0;
		$sess = array(
			'set_tahun' => $tahun,
			'set_semester' => $semester
		);
		$this->session->set_userdata( $sess );
		$this->load->model('keuangan_grafik_model');
		$header = $this->header_model->get_data();
		$nav['act_sub'] = 203;
		$header['minsidebar'] = 1;
		$this->load->view('header', $header);
		$this->load->view('nav', $nav);
		$smt = $this->session->userdata('set_semester');
		$thn = $this->session->userdata('set_tahun');

		switch ($jenis) {
			case 'grafik-R-PD':
				$this->grafik_r_pd($thn);
				break;
			case 'grafik-RP-APBD':
				$this->grafik_rp_apbd($thn);
				break;
			case 'grafik-R-BD':
				$this->grafik_r_bd($thn);
				break;
			case 'grafik-R-PEMDES';
				$this->grafik_r_pemdes($thn);
				break;
			case 'rincian_realisasi':
				$this->rincian_realisasi($thn);
				break;
			case 'rincian_realisasi_smt1':
				$this->rincian_realisasi_smt1($thn);
				break;

			default:
				$this->grafik_r_pd($thn);
				break;
		}

		$this->load->view('footer');
	}

	private function rincian_realisasi($thn)
	{
		$data = $this->keuangan_grafik_model->lap_rp_apbd($thn);
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$this->load->view('keuangan/rincian_realisasi', $data);
	}

	private function rincian_realisasi_smt1($thn)
	{
		$data = $this->keuangan_grafik_model->lap_rp_apbd_smt1($thn);
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$this->load->view('keuangan/rincian_realisasi_smt1', $data);
	}

	public function cetak($jenis)
	{
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$tahun = $this->session->userdata('set_tahun') ? $this->session->userdata('set_tahun') : $data['tahun_anggaran'][0];
		$semester = $this->session->userdata('set_semester') ? $this->session->userdata('set_semester') : 0;
		$sess = array(
			'set_tahun' => $tahun,
			'set_semester' => $semester
		);
		$this->session->set_userdata( $sess );
		$this->load->model('keuangan_grafik_model');
		$smt = $this->session->userdata('set_semester');
		$thn = $this->session->userdata('set_tahun');

		switch ($jenis) {
			case 'cetak_rincian_realisasi':
				$this->cetak_rincian_realisasi($thn);
				break;
			case 'cetak_rincian_realisasi_smt1':
				$this->cetak_rincian_realisasi_smt1($thn);
				break;
			default:
				$this->cetak_rincian_realisasi($thn);
				break;
		}
	}

	private function cetak_rincian_realisasi($thn)
	{
		$data = $this->keuangan_grafik_model->lap_rp_apbd($thn);
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$data['ta'] = $this->session->userdata('set_tahun');
		$data['sm'] = '2';
		$header = $this->header_model->get_data();
		$data['desa'] = $header['desa'];
		$this->load->view('keuangan/cetak_tabel_laporan_rp_apbd.php', $data);
	}

	private function cetak_rincian_realisasi_smt1($thn)
	{
		$data = $this->keuangan_grafik_model->lap_rp_apbd_smt1($thn);
		$data['tahun_anggaran'] = $this->keuangan_model->list_tahun_anggaran();
		$data['ta'] = $this->session->userdata('set_tahun');
		$data['sm'] = '1';
		$header = $this->header_model->get_data();
		$data['desa'] = $header['desa'];
		$this->load->view('keuangan/cetak_tabel_laporan_rp_apbd.php', $data);
	}

	private function grafik_r_pd($thn)
	{
		$data = $this->keuangan_grafik_model->r_pd($thn);
		$bidang = array();
		foreach ($data['jenis_pendapatan'] as $b)
		{
			$bidang[] = "'". $b['Nama_Jenis']. "'";
		}
		$anggaran = array();
		foreach ($data['anggaran'] as $a)
		{
      if(!empty($a['Pagu']) || !is_null($a['Pagu']))
      {
        $anggaran[] =  $a['Pagu'];
      }
      else
      {
        $anggaran[] =  0;
      }
		}
		$realisasi_pendapatan = array();
		foreach ($data['realisasi_pendapatan'] as $r)
		{
			if(!empty($r['Nilai_pendapatan']) || !is_null($r['Nilai_pendapatan']))
			{
				$realisasi_pendapatan[] =  $r['Nilai_pendapatan'];
			}
			else
			{
				$realisasi_pendapatan[] =  0;
			}
		}
		$realisasi_bunga = array();
		foreach ($data['realisasi_bunga'] as $rb)
		{
			if(!empty($rb['Nilai_bunga']) || !is_null($rb['Nilai_bunga']))
			{
				$realisasi_bunga[] =  $rb['Nilai_bunga'];
			}
			else
			{
				$realisasi_bunga[] =  0;
			}
		}
		$data_chart = array(
			'type' => $jenis,
			'thn' => $thn,
			'bidang' => $bidang,
			'anggaran' => $anggaran,
			'realisasi_pendapatan' => $realisasi_pendapatan,
			'realisasi_bunga' => $realisasi_bunga,
			'tahun_anggaran' => $this->keuangan_model->list_tahun_anggaran()
		);
		$this->load->view('keuangan/grafik_r_pd', $data_chart);
	}

	private function grafik_rp_apbd($thn)
	{
		$data = $this->keuangan_grafik_model->rp_apbd($thn);
		$bidang = array();
		foreach ($data['jenis_pelaksanaan'] as $b)
		{
			$bidang[] = "'". $b['Nama_Akun']. "'";
		}
		$anggaran = array();
		foreach ($data['anggaran'] as $a)
		{
      if(!empty($a['Pagu']) || !is_null($a['Pagu']))
      {
        $anggaran[] =  $a['Pagu'];
      }
      else
      {
        $anggaran[] =  0;
      }
		}
		$realisasi_pendapatan = array();
		foreach ($data['realisasi_pendapatan'] as $s)
		{
			if(!empty($s['Nilai_pendapatan']) || !is_null($s['Nilai_pendapatan']))
			{
				$realisasi_pendapatan[] =  $s['Nilai_pendapatan'];
			}
			else
			{
				$realisasi_pendapatan[] =  0;
			}
		}
		$realisasi_bunga = array();
		foreach ($data['realisasi_bunga'] as $sb)
		{
			if(!empty($sb['Nilai_bunga']) || !is_null($sb['Nilai_bunga']))
			{
				$realisasi_bunga[] =  $sb['Nilai_bunga'];
			}
			else
			{
				$realisasi_bunga[] =  0;
			}
		}
		$realisasi_belanja = array();
		foreach ($data['realisasi_belanja'] as $sbl)
		{
			if(!empty($sbl['Nilai_belanja']) || !is_null($sbl['Nilai_belanja']))
			{
				$realisasi_belanja[] =  $sbl['Nilai_belanja'];
			}
			else
			{
				$realisasi_belanja[] =  0;
			}
		}
		$realisasi_biaya = array();
		foreach ($data['realisasi_biaya'] as $sby)
		{
			if(!empty($sby['Nilai_biaya']) || !is_null($sby['Nilai_biaya']))
			{
				$realisasi_biaya[] =  $sby['Nilai_biaya'];
			}
			else
			{
				$realisasi_biaya[] =  0;
			}
		}
		$data_chart = array(
			'type' => $jenis,
			'thn' => $thn,
			'bidang' => $bidang,
			'anggaran' => $anggaran,
			'realisasi_pendapatan' => $realisasi_pendapatan,
			'realisasi_belanja' => $realisasi_belanja,
			'realisasi_bunga' => $realisasi_bunga,
			'realisasi_biaya' => $realisasi_biaya,
			'tahun_anggaran' => $this->keuangan_model->list_tahun_anggaran()
		);
		$this->load->view('keuangan/grafik_rp_apbd', $data_chart);
	}

	private function grafik_r_bd($thn)
	{
		$data = $this->keuangan_grafik_model->r_bd($thn);
		$bidang = array();
		foreach ($data['jenis_belanja'] as $b)
		{
			$bidang[] = "'". $b['Nama_Jenis']. "'";
		}
		$anggaran = array();
		foreach ($data['anggaran'] as $a)
		{
      if(!empty($a['Pagu']) || !is_null($a['Pagu']))
      {
        $anggaran[] =  $a['Pagu'];
      }
      else
      {
        $anggaran[] =  0;
      }
		}
		$realisasi_belanja = array();
		foreach ($data['realisasi_belanja'] as $r)
		{
			if(!empty($r['Nilai_belanja']) || !is_null($r['Nilai_belanja']))
			{
				$realisasi_belanja[] =  $r['Nilai_belanja'];
			}
			else
			{
				$realisasi_belanja[] =  0;
			}
		}
		$data_chart = array(
			'type' => $jenis,
			'smt' => $smt,
			'thn' => $thn,
			'bidang' => $bidang,
			'anggaran' => $anggaran,
			'realisasi_belanja' => $realisasi_belanja,
			'tahun_anggaran' => $this->keuangan_model->list_tahun_anggaran()
		);
		$this->load->view('keuangan/grafik_r_bd', $data_chart);
	}

	private function grafik_r_pemdes($thn)
	{
		$data = $this->keuangan_grafik_model->r_pembiayaan($thn);
		$bidang = array();
		foreach ($data['pembiayaan'] as $b)
		{
			$bidang[] = "'". $b['Nama_Akun']. "'";
		}
		$anggaran = array();
		foreach ($data['anggaran'] as $a)
		{
      if(!empty($a['Pagu']) || !is_null($a['Pagu']))
      {
        $anggaran[] =  $a['Pagu'];
      }
      else
      {
        $anggaran[] =  0;
      }
		}
		$realisasi_biaya = array();
		foreach ($data['realisasi_biaya'] as $r)
		{
			if(!empty($r['Nilai_biaya']) || !is_null($r['Nilai_biaya']))
			{
				$realisasi_biaya[] =  $r['Nilai_biaya'];
			}
			else
			{
				$realisasi_biaya[] =  0;
			}
		}
		$data_chart = array(
			'type' => $jenis,
			'smt' => $smt,
			'thn' => $thn,
			'bidang' => $bidang,
			'anggaran' => $anggaran,
			'realisasi_biaya' => $realisasi_biaya,
			'tahun_anggaran' => $this->keuangan_model->list_tahun_anggaran()
		);
		$this->load->view('keuangan/grafik_r_pemdes', $data_chart);
	}

	public function impor_data()
	{
		$data['main'] = $this->keuangan_model->list_data();
		$data['form_action'] = site_url("keuangan/proses_impor");
		$header = $this->header_model->get_data();
		$nav['act_sub'] = 202;
		$this->load->view('header', $header);
		$this->load->view('nav', $nav);
		$this->load->view('keuangan/impor_data', $data);
		$this->load->view('footer');
	}

	public function proses_impor()
	{
		if (empty($_FILES['keuangan']['name']))
		{
			$this->session->success = -1;
			$this->session->error_msg = "Tidak ada file untuk diimpor";
			redirect('keuangan/impor_data');
		}
		if ($_POST['jenis_impor'] == 'update')
		{
			$this->keuangan_model->extract_update();
		}
		else
		{
			$this->keuangan_model->extract();
		}
		redirect('keuangan/impor_data');
	}

	public function cek_versi_database()
	{
		$nama = $_FILES['keuangan'];
		$file_parts = pathinfo($nama['name']);
		if ($file_parts['extension'] === 'zip')
		{
			$cek = $this->keuangan_model->cek_keuangan_master($nama);
			if ($cek == -1)
			{
				echo json_encode(2);
			}
			else if ($cek)
			{
				$output =array('id' => $cek->id, 'tahun_anggaran' => $cek->tahun_anggaran);
				echo json_encode($output);
			}
			else
			{
				echo json_encode(0);
			}
		}
		else
		{
			echo json_encode(1);
		}
	}

	// data tahun anggaran untuk keperluan dropdown pada plugin keuangan di text editor
	public function cek_tahun()
	{
		$data = $this->keuangan_model->list_tahun_anggaran();
		$list_tahun = array();
		foreach ($data as $tahun)
		{
			$list_tahun[] = array(
				'text' => $tahun,
				'value' => $tahun
			);
		}
		echo json_encode($list_tahun);
	}

	public function delete($id = '')
	{
		$this->redirect_hak_akses('h', 'keuangan');
		$_SESSION['success'] = 1;
		$outp = $this->keuangan_model->delete($id);
		if (!$outp) $_SESSION['success'] = -1;
		redirect('keuangan/impor_data');
	}

	public function pilih_desa($id_master)
	{
		$data['desa_ganda'] = $this->keuangan_model->cek_desa($id_master);
		$data['id_master'] = $id_master;
		$this->load->view('keuangan/pilih_desa', $data);
	}

	public function bersihkan_desa($id_master)
	{
		$this->keuangan_model->bersihkan_desa($id_master, $this->input->post('kode_desa'));
		redirect('keuangan/impor_data');
	}
}
